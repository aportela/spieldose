<template>
  <div id="analog-vu-meter-container">
    <div class="vu-scale">
      <div class="arc"></div>
      <div class="mark m-20"><span>-20</span></div>
      <div class="mark m-10"><span>-10</span></div>
      <div class="mark m-7"><span>-7</span></div>
      <div class="mark m-5"><span>-5</span></div>
      <div class="mark m-3"><span>-3</span></div>
      <div class="mark m-2"><span>-2</span></div>
      <div class="mark m-1"><span>-1</span></div>
      <div class="mark m0"><span>0</span></div>
      <div class="mark m1"><span>+1</span></div>
      <div class="mark m2"><span>+2</span></div>
      <div class="mark m3"><span>+3</span></div>
    </div>
    <span id="vu-bottom-label">VU</span>
    <span id="vu-bottom-circle"></span>
    <canvas id="vu-meter-canvas"></canvas>
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
let canvas = null;
let ctx = null;
let displayedEnergy = 0;
let lastTime = 0;
const maxFPS = 120;
const fpsInterval = 1000 / maxFPS;

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
      refreshVuMeter();
    }
  }
});

const createAudioMotionAnalyzer = (defaultOptions, start) => {
  if (!analyzer.value) {
    analyzer.value = new AudioMotionAnalyzer(
      document.getElementById('vu-meter-canvas'),
      defaultOptions
    );
    if (!audioMotionAnalyzerStore.hasOtherRuningInstances) {
      audioMotionAnalyzerStore.instance();
    }
    if (start) {
      analyzer.value.start();
      refreshVuMeter();
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

const createVumeterCanvas = () => {
  canvas = document.getElementById('vu-meter-canvas');
  ctx = canvas.getContext('2d');
}

const smoothEnergy = (target) => {
  displayedEnergy += (target - displayedEnergy) * 0.1; // smoot factor
  return displayedEnergy;
}

const mapEnergyToAngle = (energy) => {
  const minAngle = -60; //-65;
  const maxAngle = +60; // +70;
  return minAngle + (maxAngle - minAngle) * energy;
}

const drawCanvasVuMeterBar = (angle) => {
  const centerX = canvas.width / 2;
  const centerY = canvas.height;
  const radius = canvas.height + (canvas.height / 10); // vu-meter bar length

  // clear previous canvas value
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  ctx.save();
  ctx.translate(centerX, centerY);
  ctx.rotate((angle * Math.PI) / 180);
  ctx.beginPath();
  ctx.moveTo(0, 0);
  ctx.lineTo(0, -radius);
  ctx.lineWidth = 1;
  ctx.strokeStyle = '#111';
  ctx.stroke();
  ctx.restore();
}

const refreshVuMeter = (timestamp) => {
  const elapsed = timestamp - lastTime;
  if (elapsed > fpsInterval) {
    lastTime = timestamp - (elapsed % fpsInterval);
    const energy = smoothEnergy(analyzer.value.getEnergy());
    const angle = mapEnergyToAngle(energy);
    drawCanvasVuMeterBar(angle);
  }
  // TODO: limit fps
  requestAnimationFrame(refreshVuMeter);
};

onMounted(() => {
  createVumeterCanvas();
  drawCanvasVuMeterBar(mapEnergyToAngle(0));
  // TODO: WARNING: on empty playlists js console show warning about AudioContext auto start denied
  createAudioMotionAnalyzer(defaultAnalyzerOptions, playerStore.hasPreviousUserInteractions);
});

onBeforeUnmount(() => {
  destroyAudioMotionAnalyzer();
});

</script>

<style lang="css">
div#analog-vu-meter-container {
  overflow: hidden;
  position: relative;
  width: 100%;
  height: 100%;
  background: radial-gradient(circle at 50% 95%,
      #f4c77a 0%,
      #dba55a 35%,
      #a06a2f 70%,
      #4a3219 100%);
}

.vu-scale {
  width: 100%;
  height: 100%;
  position: absolute;
  top: 0px;
  left: 50%;
  transform: translateX(-50%);
  overflow: hidden;
}


/* top scale arc */
.arc {
  width: 95%;
  aspect-ratio: 1 / 1;
  /*
  height: 90%;
  border-top: 3px solid #f4c77a;
  border-left: 3px solid #f4c77a;
  border-right: 3px solid #f4c77a;
  */
  /*
  border-top: 5px solid #222;
  border-left: 3px solid #222;
  border-right: 3px solid #222;
  border-left: none;
  border-right: none;
  border-bottom: none;
  */
  border: 2px solid #222;
  /*
  border-radius: 320px 320px 0 0;
  */
  border-radius: 50%;
  position: absolute;
  bottom: -115%;
  left: 50%;
  transform: translateX(-50%);
}

/* top scale mark labels */
.mark {
  position: absolute;
  left: 50%;
  top: 18%;
  transform-origin: 50% 1300%;
  text-align: center;
  font-family: Arial, sans-serif;
  color: #222;
}

/* range line */
.mark::before {
  content: "";
  display: block;
  width: 2px;
  height: 16px;
  background: #222;
}

/* range label */
.mark span {
  position: absolute;
  top: -25px;
  left: -4px;
  display: block;
  font-weight: bold;
}

.m-20 {
  --angle: -51deg;
  transform: rotate(var(--angle));
}

.m-10 {
  --angle: -43deg;
  transform: rotate(var(--angle));
}

.m-7 {
  --angle: -34deg;
  transform: rotate(var(--angle));
}

.m-5 {
  --angle: -26deg;
  transform: rotate(var(--angle));
}

.m-3 {
  --angle: -19deg;
  transform: rotate(var(--angle));
}

.m-2 {
  --angle: -12deg;
  transform: rotate(var(--angle));
}

.m-1 {
  --angle: -6deg;
  transform: rotate(var(--angle));
}

.m0 {
  --angle: 0deg;
  transform: rotate(var(--angle));
}

.m1 {
  --angle: 17deg;
  transform: rotate(var(--angle));
  color: rgb(241, 10, 10);
}

.m2 {
  --angle: 34deg;
  transform: rotate(var(--angle));
  color: rgb(241, 10, 10);
}

.m3 {
  --angle: 51deg;
  transform: rotate(var(--angle));
  color: rgb(241, 10, 10);
}

span#vu-bottom-label {
  display: block;
  position: absolute;
  bottom: 30px;
  width: 100%;
  text-align: center;
  font-size: 300%;
  font-weight: bold;
  color: #472a0f;
}

span#vu-bottom-circle {
  display: block;
  position: absolute;
  bottom: -20px;
  width: 40px;
  height: 40px;
  background: #472a0f;
  border-radius: 50%;
  border: 1px solid #000;
  left: 50%;
  transform: translateX(-50%);
  opacity: 0.4;
}

canvas#vu-meter-canvas {
  width: 100%;
  height: 90%;
  position: absolute;
  top: 10%;
  left: 0px;
}
</style>