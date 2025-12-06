<template>
  <div id="spieldose-sidebar-analyzer-container" class="cursor-pointer"
    :style="{ height: sidebarMiniSpectrumAnalyzerSettingsStore.currentHeight + 'px' }"
    :title="t('Toggle analyzer octave bands number')" @click="onToggleCurrentMode"></div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import { AudioMotionAnalyzer, type ConstructorOptions as AudioMotionAnalyzerConstructorOptionsInterface, type GradientOptions as GradientOptionsInterface } from "audiomotion-analyzer";
import { usePlayerStore } from "src/stores/player";
import { useAudioMotionAnalyzerStore } from "src/stores/audioMotionAnalyzer";
import { useSidebarMiniSpectrumAnalyzerSettingsStore } from "src/stores/sidebarMiniSpectrumAnalyzerSettings";

const { t } = useI18n();

const playerStore = usePlayerStore();
const audioMotionAnalyzerStore = useAudioMotionAnalyzerStore();
const sidebarMiniSpectrumAnalyzerSettingsStore = useSidebarMiniSpectrumAnalyzerSettingsStore();

const analyzerInstance = ref<AudioMotionAnalyzer | null>(null);

const defaultAnalyzerConstructorOptions: AudioMotionAnalyzerConstructorOptionsInterface = {
  source: audioMotionAnalyzerStore.audioInstance,
  connectSpeakers: audioMotionAnalyzerStore.connectSpeakers,
  start: false,
};

const defaultAnalyzerOptions = {
  showCanvas: true,
  source: audioMotionAnalyzerStore.audioInstance,
  connectSpeakers: audioMotionAnalyzerStore.connectSpeakers,
  start: false,
  loRes: sidebarMiniSpectrumAnalyzerSettingsStore.isLoResActive,
  maxFPS: sidebarMiniSpectrumAnalyzerSettingsStore.currentFPS,
  mode: sidebarMiniSpectrumAnalyzerSettingsStore.currentMode,
  ledBars: sidebarMiniSpectrumAnalyzerSettingsStore.ledBarsActive,
  showPeaks: sidebarMiniSpectrumAnalyzerSettingsStore.showPeaks,
  trueLeds: sidebarMiniSpectrumAnalyzerSettingsStore.trueLedsActive,
  barSpace: sidebarMiniSpectrumAnalyzerSettingsStore.currentBarSpace,
  showScaleX: false,
  showScaleY: false,
  channelLayout: sidebarMiniSpectrumAnalyzerSettingsStore.currentChannelLayout,
  colorMode: sidebarMiniSpectrumAnalyzerSettingsStore.currentColorMode,
  splitGradient: false,
  bgAlpha: 1,
  overlay: true,
  showBgColor: true
};

watch(() => playerStore.hasPreviousUserInteractions, (newValue) => {
  if (newValue) {
    if (analyzerInstance.value === null) {
      createAudioMotionAnalyzerInstance(defaultAnalyzerConstructorOptions, defaultAnalyzerOptions, true);
    } else {
      startAnalyzer();
    }
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.currentMode, (newValue) => {
  if (analyzerInstance.value && (newValue == 10 || (newValue >= 0 && newValue <= 8))) {
    analyzerInstance.value.setOptions({ mode: newValue, barSpace: sidebarMiniSpectrumAnalyzerSettingsStore.currentBarSpace });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.currentBarSpace, (newValue) => {
  if (analyzerInstance.value && newValue >= 0 && newValue <= 1) {
    analyzerInstance.value.setOptions({ mode: sidebarMiniSpectrumAnalyzerSettingsStore.currentMode, barSpace: newValue });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.currentFPS, (newValue) => {
  if (analyzerInstance.value && newValue >= 0 && newValue <= 144) {
    analyzerInstance.value.setOptions({ maxFPS: newValue });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.currentHeight, (newValue) => {
  if (analyzerInstance.value && newValue > 0) {
    analyzerInstance.value.setOptions({ height: newValue });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.currentChannelLayout, (newValue) => {
  if (analyzerInstance.value) {
    analyzerInstance.value.setOptions({ channelLayout: newValue });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.currentColorMode, (newValue) => {
  if (analyzerInstance.value) {
    analyzerInstance.value.setOptions({ colorMode: newValue });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.currentGradient, (newValue) => {
  if (analyzerInstance.value) {
    analyzerInstance.value.setOptions({ gradient: newValue });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.showPeaks, (newValue) => {
  if (analyzerInstance.value) {
    analyzerInstance.value.setOptions({ showPeaks: newValue });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.ledBarsActive, (newValue) => {
  if (analyzerInstance.value) {
    analyzerInstance.value.setOptions({ ledBars: newValue });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.trueLedsActive, (newValue) => {
  if (analyzerInstance.value) {
    analyzerInstance.value.setOptions({ trueLeds: newValue });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.isLoResActive, (newValue) => {
  if (analyzerInstance.value) {
    analyzerInstance.value.setOptions({ loRes: newValue });
  }
});

const onToggleCurrentMode = () => {
  let mode = sidebarMiniSpectrumAnalyzerSettingsStore.currentMode;
  if (++mode > 8) {
    mode = 1;
  }
  sidebarMiniSpectrumAnalyzerSettingsStore.setMode(mode);
};

const startAnalyzer = (): void => {
  if (analyzerInstance.value !== null) {
    if (!analyzerInstance.value.isOn) {
      analyzerInstance.value.start();
    }
  } else {
    console.error("AudioMotion analyzer instance is null");
  }
};

const createAudioMotionAnalyzerInstance = (constructorOptions: AudioMotionAnalyzerConstructorOptionsInterface, defaultOptions: AudioMotionAnalyzerConstructorOptionsInterface, start: boolean) => {
  if (analyzerInstance.value === null) {
    const constainerElement = document.getElementById('spieldose-sidebar-analyzer-container');
    if (constainerElement !== null) {
      analyzerInstance.value = new AudioMotionAnalyzer(
        constainerElement,
        constructorOptions
      );
      if (!audioMotionAnalyzerStore.hasOtherRuningInstances) {
        audioMotionAnalyzerStore.instance();
      }
      analyzerInstance.value.setOptions(defaultOptions);
      const gradientOptions: GradientOptionsInterface = {
        bgColor: '#fff',
        //dir: 'v',
        colorStops: [
          { color: '#d30320', level: 0.9 },
          { color: '#d72c43', level: 0.8 },
          { color: '#db5063', level: 0.6 },
          { color: '#de6b7b', level: 0.4 },
          { color: '#e399a3', level: 0.2 }
        ]
      }
      analyzerInstance.value.registerGradient('spieldose', gradientOptions);
      analyzerInstance.value.gradient = sidebarMiniSpectrumAnalyzerSettingsStore.currentGradient;
      if (start) {
        startAnalyzer();
      }
    }
  }
};

const destroyAudioMotionAnalyzerInstance = () => {
  if (analyzerInstance.value !== null) {
    analyzerInstance.value.stop();
    // TODO: WARNING: possible leak, next line stops audio (check disconnectInput method before destroy)
    //analyzerInstance.value.destroy();
  }
};

onMounted(() => {
  // TODO: WARNING: on empty playlists js console show warning about AudioContext auto start denied
  createAudioMotionAnalyzerInstance(defaultAnalyzerConstructorOptions, defaultAnalyzerOptions, playerStore.hasPreviousUserInteractions);
});

onBeforeUnmount(() => {
  destroyAudioMotionAnalyzerInstance();
});

</script>

<style lang="css">
div#spieldose-sidebar-analyzer-container {
  width: 100%;
  /* height: 40px; */
}

canvas {
  display: block;
  width: 100%;
}
</style>