<template>
  <canvas id="vu-meter" style="width: 100%; height: 200px"></canvas>
  <div id="spieldose-sidebar-analyzer-container" class="cursor-pointer"
    :style="{ height: miniSpectrumAnalyzerSettingsStore.currentHeight + 'px' }"
    :title="t('Toggle analyzer octave bands number')" @click="onToggleCurrentMode"></div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import AudioMotionAnalyzer from "audiomotion-analyzer";
import { usePlayerStore } from "src/stores/player";
import { useMiniSpectrumAnalyzerSettingsStore } from "src/stores/miniSpectrumAnalyzerSettings";

const { t } = useI18n();

const playerStore = usePlayerStore();
const miniSpectrumAnalyzerSettingsStore = useMiniSpectrumAnalyzerSettingsStore();

const analyzer = ref(null);

const defaultAnalyzerOptions = {
  source: playerStore.audioInstance,
  start: false,
  maxFPS: miniSpectrumAnalyzerSettingsStore.currentFPS,
  mode: miniSpectrumAnalyzerSettingsStore.currentMode,
  ledBars: miniSpectrumAnalyzerSettingsStore.ledBarsActive,
  showPeaks: miniSpectrumAnalyzerSettingsStore.showPeaks,
  trueLeds: miniSpectrumAnalyzerSettingsStore.trueLedsActive,
  barSpace: miniSpectrumAnalyzerSettingsStore.currentBarSpace,
  showScaleX: false,
  showScaleY: false,
  channelLayout: miniSpectrumAnalyzerSettingsStore.currentChannelLayout,
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

watch(() => miniSpectrumAnalyzerSettingsStore.currentMode, (newValue) => {
  if (analyzer.value && newValue >= 0 && newValue <= 144) {
    analyzer.value.setOptions({ mode: newValue, barSpace: miniSpectrumAnalyzerSettingsStore.currentBarSpace });
  }
});

watch(() => miniSpectrumAnalyzerSettingsStore.currentBarSpace, (newValue) => {
  if (analyzer.value && newValue >= 0 && newValue <= 1) {
    analyzer.value.setOptions({ mode: miniSpectrumAnalyzerSettingsStore.currentMode, barSpace: newValue });
  }
});

watch(() => miniSpectrumAnalyzerSettingsStore.currentFPS, (newValue) => {
  if (analyzer.value && newValue >= 0 && newValue <= 144) {
    analyzer.value.setOptions({ maxFPS: newValue });
  }
});

watch(() => miniSpectrumAnalyzerSettingsStore.currentHeight, (newValue) => {
  if (analyzer.value && newValue > 0) {
    analyzer.value.setOptions({ height: newValue });
  }
});

watch(() => miniSpectrumAnalyzerSettingsStore.currentChannelLayout, (newValue) => {
  if (analyzer.value) {
    analyzer.value.setOptions({ channelLayout: newValue });
  }
});

watch(() => miniSpectrumAnalyzerSettingsStore.currentGradient, (newValue) => {
  if (analyzer.value) {
    analyzer.value.setOptions({ gradient: newValue });
  }
});

watch(() => miniSpectrumAnalyzerSettingsStore.showPeaks, (newValue) => {
  if (analyzer.value) {
    analyzer.value.setOptions({ showPeaks: newValue });
  }
});

watch(() => miniSpectrumAnalyzerSettingsStore.ledBarsActive, (newValue) => {
  if (analyzer.value) {
    analyzer.value.setOptions({ ledBars: newValue });
  }
});

watch(() => miniSpectrumAnalyzerSettingsStore.trueLedsActive, (newValue) => {
  if (analyzer.value) {
    analyzer.value.setOptions({ trueLeds: newValue });
  }
});

const onToggleCurrentMode = () => {
  let mode = miniSpectrumAnalyzerSettingsStore.currentMode;
  if (++mode > 8) {
    mode = 1;
  }
  miniSpectrumAnalyzerSettingsStore.setMode(mode);
};

const createAudioMotionAnalyzer = (defaultOptions, start) => {
  if (!analyzer.value) {
    analyzer.value = new AudioMotionAnalyzer(
      document.getElementById('spieldose-sidebar-analyzer-container'),
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
    playerStore.setAudioMotionAnalyzerSource(analyzer.value.connectedSources[0]);
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

function getRMS(values) {
  /*
  let sumSquares = 0;
  for (let i = 0; i < values.length; i++) {
    sumSquares += values[i] * values[i];
  }
  return Math.sqrt(sumSquares / values.length);
  */
}

let canvas = null;
let ctx = null;
let rect = null;
let img = null;
function createVumeter() {
  canvas = document.getElementById('vu-meter');
  ctx = canvas.getContext('2d');
  img = new Image();
  img.src = "images/vu-meter.png";
}

let displayedEnergy = 0;
function smoothEnergy(target) {
  displayedEnergy += (target - displayedEnergy) * 0.1; // factor de suavizado
  return displayedEnergy;
}


function drawNeedle(angle) {

  const centerX = canvas.width / 2;
  const centerY = canvas.height - 10;
  const radius = 100;

  ctx.clearRect(0, 0, canvas.width, canvas.height);

  ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

  ctx.save();
  ctx.translate(centerX, centerY);
  ctx.rotate((angle * Math.PI) / 180);
  ctx.beginPath();
  ctx.moveTo(0, 0);
  ctx.lineTo(0, -radius + 3);
  ctx.lineWidth = 1;
  ctx.strokeStyle = '#222';
  ctx.stroke();
  ctx.restore();
}
function mapEnergyToAngle(energy) {
  const minAngle = -65;
  const maxAngle = +90;
  return minAngle + (maxAngle - minAngle) * energy;
}

function updateVU() {
  const energy = smoothEnergy(analyzer.value.getEnergy());

  const angle = mapEnergyToAngle(energy);
  drawNeedle(angle);

  requestAnimationFrame(updateVU);
}

onMounted(() => {
  // TODO: WARNING: on empty playlists js console show warning about AudioContext auto start denied
  createAudioMotionAnalyzer(defaultAnalyzerOptions, playerStore.hasPreviousUserInteractions);
  createVumeter();
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