<template>
  <div id="peak-meter-container">
    <canvas id="peak-meter-canvas"></canvas>
  </div>
</template>

<script setup lang="ts">

  import { ref, watch, onMounted, onBeforeUnmount } from "vue";
  import { AudioMotionAnalyzer, type ConstructorOptions as AudioMotionAnalyzerConstructorOptionsInterface } from "audiomotion-analyzer";
  import { useCurrentPlayListsStore } from "src/stores/currentPlayLists";
  import { useAudioMotionAnalyzerStore } from "src/stores/audioMotionAnalyzer";

  const currentPlayListsStore = useCurrentPlayListsStore();
  const audioMotionAnalyzerStore = useAudioMotionAnalyzerStore();

  const analyzerInstance = ref<AudioMotionAnalyzer | null>(null);
  let canvas: HTMLCanvasElement | null;
  let ctx: CanvasRenderingContext2D | null;
  //const maxFPS: number = 60;
  //const fpsInterval: number = 1000 / maxFPS;

  const defaultAnalyzerConstructorOptions: AudioMotionAnalyzerConstructorOptionsInterface = {
    source: audioMotionAnalyzerStore.audioInstance,
    connectSpeakers: audioMotionAnalyzerStore.connectSpeakers,
    start: false,
  };

  const defaultAnalyzerOptions: AudioMotionAnalyzerConstructorOptionsInterface = {
    useCanvas: false,
    channelLayout: "dual-horizontal",
    maxFPS: 60,
    mode: 8, // 10 bands (min)
  };

  watch(() => currentPlayListsStore.playerHasPreviousUserInteractions, (newValue) => {
    if (newValue) {
      if (analyzerInstance.value === null) {
        createAudioMotionAnalyzerInstance(defaultAnalyzerConstructorOptions, defaultAnalyzerOptions, true);
      }
      else {
        startAnalyzer();
      }
    }
  });

  const setupCanvas = (): boolean => {
    canvas = document.getElementById('peak-meter-canvas') as HTMLCanvasElement | null;
    if (canvas !== null) {
      ctx = canvas.getContext('2d');
      if (ctx === null) {
        console.error("Error gettting canvas 2d context");
        return (false);
      } else {
        return (true);
      }
    } else {
      console.error("Error getting canvas element");
      return (false);
    }
  }

  const startAnalyzer = (): void => {
    if (analyzerInstance.value !== null) {
      if (!analyzerInstance.value.isOn) {
        analyzerInstance.value.start();
      }
      animationId = requestAnimationFrame(updateBars);
    } else {
      console.error("AudioMotion analyzer instance is null");
    }
  };

  const createAudioMotionAnalyzerInstance = (constructorOptions: AudioMotionAnalyzerConstructorOptionsInterface, defaultOptions: AudioMotionAnalyzerConstructorOptionsInterface, start: boolean) => {
    if (analyzerInstance.value === null) {
      analyzerInstance.value = new AudioMotionAnalyzer(
        //document.getElementById('vu-meter-canvas')!,
        constructorOptions
      );
      if (!audioMotionAnalyzerStore.hasOtherRuningInstances) {
        audioMotionAnalyzerStore.instance();
      }
      analyzerInstance.value.setOptions(defaultOptions);
    }
    if (start) {
      startAnalyzer();
    }
  };

  const destroyAudioMotionAnalyzerInstance = () => {
    if (analyzerInstance.value !== null) {
      analyzerInstance.value.stop();
      // TODO: WARNING: possible leak, next line stops audio (check disconnectInput method before destroy)
      //analyzerInstance.value.destroy();
    }
  };

  let animationId: number | null = null;

  const drawHorizontalBar = (barIndex: number, barValue: number, offsetY: number) => {
    const barWidth = canvas!.width - 20;
    const barHeight = canvas!.height / 4;
    ctx!.fillStyle = '#333';
    ctx!.fillRect(20, offsetY, barWidth, barHeight);
    const valueWidth = barValue * barWidth;
    ctx!.fillStyle = '#0f0';  // Barra de color verde
    ctx!.fillRect(20, offsetY, valueWidth, barHeight);
  }

  const updateBars = () => {
    const bars = analyzerInstance.value!.getBars();
    if (!bars || bars.length === 0) return;
    ctx!.clearRect(0, 0, canvas!.width, canvas!.height);
    let leftMax = 0;
    let rightMax = 0;
    bars.forEach(bar => {
      const { value } = bar;
      //leftMax = Math.max(leftMax, value[0]);
      //rightMax = Math.max(rightMax, value[1]);
      leftMax += value[0];
      if (value[1]) {
        rightMax += value[1];
      }
    });
    leftMax = leftMax / bars.length;
    rightMax = rightMax / bars.length;
    drawHorizontalBar(0, leftMax, 0);
    drawHorizontalBar(1, rightMax, (canvas!.height / 2) + 20);
    animationId = requestAnimationFrame(updateBars);
  }

  onMounted(() => {
    if (setupCanvas()) {
      if (currentPlayListsStore.playerHasPreviousUserInteractions) {
        createAudioMotionAnalyzerInstance(defaultAnalyzerConstructorOptions, defaultAnalyzerOptions, true);
      }
    } else {
      console.error("Error setting up vumeter canvas");
    }
  });

  onBeforeUnmount(() => {
    if (animationId !== null) {
      cancelAnimationFrame(animationId);
    }
    destroyAudioMotionAnalyzerInstance();
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
    width: 88%;
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