<template>
  <div
    style="position: relative; width: 100%; height: 200px; background-color: #c87f2c; background2: url('images/vu-meter.png') no-repeat">
    <span
      style="display: block; position: absolute; bottom: 30px;width: 100%;text-align: center;font-size: 50px; font-weight: bold; color: #472a0f;">VU</span>

    <canvas id="vu-meter" style="width: 100%; height: 200px;"></canvas>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import AudioMotionAnalyzer from "audiomotion-analyzer";
import { usePlayerStore } from "src/stores/player";
import { useAudioMotionAnalyzerStore } from "src/stores/audioMotionAnalyzer";
import { useMiniSpectrumAnalyzerSettingsStore } from "src/stores/miniSpectrumAnalyzerSettings";

const playerStore = usePlayerStore();
const audioMotionAnalyzerStore = useAudioMotionAnalyzerStore();
const miniSpectrumAnalyzerSettingsStore = useMiniSpectrumAnalyzerSettingsStore();

const analyzer = ref(null);


const defaultAnalyzerOptions = {
  showCanvas: false,
  source: audioMotionAnalyzerStore.audioInstance,
  connectSpeakers: audioMotionAnalyzerStore.connectSpeakers,
  start: false,
  maxFPS: miniSpectrumAnalyzerSettingsStore.currentFPS,
  mode: 8,
  ledBars: true,
  showPeaks: false,
  trueLeds: true,
  barSpace: miniSpectrumAnalyzerSettingsStore.currentBarSpace,
  showScaleX: false,
  showScaleY: false,
  channelLayout: "single",
  colorcurrentMode: 'gradient',
  splitGradient: false,
  bgAlpha: 1,
  overlay: true,
  showBgColor: true
};

watch(() => playerStore.hasPreviousUserInteractions, (newValue) => {
  if (!analyzer.value) {
    if (newValue) {
      createAudioMotionAnalyzer(defaultAnalyzerOptions, true);
    }
  } else {
    if (newValue) {
      analyzer.value.start();
      updateVU();
    }
  }
});

const createAudioMotionAnalyzer = (defaultOptions, start) => {
  if (!analyzer.value) {
    analyzer.value = new AudioMotionAnalyzer(
      document.getElementById('vu-meter'),
      defaultOptions
    );
    const gradientOptions = {
      bgColor: '#fff',
      dir: 'v',
      colorStops: [
        { color: '#d30320', level: 0.9 },
        { color: '#d72c43', level: 0.8 },
        { color: '#db5063', level: 0.6 },
        { color: '#de6b7b', level: 0.4 },
        { color: '#e399a3', level: 0.2 }
      ]
    }
    analyzer.value.registerGradient('spieldose', gradientOptions);
    analyzer.value.gradient = miniSpectrumAnalyzerSettingsStore.currentGradient;
    if (!audioMotionAnalyzerStore.hasOtherRuningInstances) {
      console.log("no habia otras");
      audioMotionAnalyzerStore.instance();
    } else {
      console.log("si habia otras");
    }
    if (start) {
      analyzer.value.start();
    }

  }
};

const destroyAudioMotionAnalyzer = () => {
  if (analyzer.value) {
    analyzer.value.stop();
    // TODO: stops audio
    //analyzer.value.destroy();
  }
};

let canvas = null;
let ctx = null;
function createVumeter() {
  canvas = document.getElementById('vu-meter');
  ctx = canvas.getContext('2d');
}

let displayedEnergy = 0;

function smoothEnergy(target) {
  displayedEnergy += (target - displayedEnergy) * 0.1; // smoot factor
  return displayedEnergy;
}

function mapEnergyToAngle(energy) {
  const minAngle = -65;
  const maxAngle = +70;
  return minAngle + (maxAngle - minAngle) * energy;
}

function drawCanvas(angle) {
  const centerX = canvas.width / 2;
  const centerY = canvas.height - 10;
  const radius = 100;

  ctx.clearRect(0, 0, canvas.width, canvas.height);

  ctx.save();
  ctx.translate(centerX, centerY);
  ctx.rotate((angle * Math.PI) / 180);
  ctx.beginPath();
  ctx.moveTo(0, 0);
  ctx.lineTo(0, -radius + 3);
  ctx.lineWidth = 1;
  ctx.strokeStyle = '#111';
  ctx.stroke();
  ctx.restore();
}


let lastTime = 0;
const fps = 120;  // FPS deseado

const fpsInterval = 1000 / fps;

function updateVU(timestamp) {
  const elapsed = timestamp - lastTime;
  if (elapsed > fpsInterval) {
    lastTime = timestamp - (elapsed % fpsInterval);
    const energy = smoothEnergy(analyzer.value.getEnergy());
    const angle = mapEnergyToAngle(energy);
    drawCanvas(angle);
  }
  // TODO: limit fps
  requestAnimationFrame(updateVU);
}

onMounted(() => {
  // TODO: WARNING: on empty playlists js console show warning about AudioContext auto start denied
  createAudioMotionAnalyzer(defaultAnalyzerOptions, playerStore.hasPreviousUserInteractions);
  createVumeter();
  drawCanvas(mapEnergyToAngle(0.5));
});


onBeforeUnmount(() => {
  destroyAudioMotionAnalyzer();
});

</script>

<style lang="css">
div#spieldose-sidebar-analyzer-container {
  width: 100%;
  /* height: 40px; */
}
</style>