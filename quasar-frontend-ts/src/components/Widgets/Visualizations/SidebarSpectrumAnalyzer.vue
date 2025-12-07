<template>
  <div id="spieldose-sidebar-analyzer-container" class="cursor-pointer"
    :style="{ height: sidebarSpectrumAnalyzerSettingsStore.height + 'px' }"
    :title="t('Toggle analyzer octave bands number')" @click="onToggleCurrentMode"></div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import { AudioMotionAnalyzer, type ConstructorOptions as AudioMotionAnalyzerConstructorOptionsInterface, type GradientOptions as GradientOptionsInterface } from "audiomotion-analyzer";
import { usePlayerStore } from "src/stores/player";
import { useAudioMotionAnalyzerStore } from "src/stores/audioMotionAnalyzer";
import { useSidebarSpectrumAnalyzerSettingsStore } from "src/stores/sidebarSpectrumAnalyzerSettings";

const { t } = useI18n();

const playerStore = usePlayerStore();
const audioMotionAnalyzerStore = useAudioMotionAnalyzerStore();
const sidebarSpectrumAnalyzerSettingsStore = useSidebarSpectrumAnalyzerSettingsStore();

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
  loRes: sidebarSpectrumAnalyzerSettingsStore.loRes,
  maxFPS: sidebarSpectrumAnalyzerSettingsStore.fps,
  mode: sidebarSpectrumAnalyzerSettingsStore.mode,
  ledBars: sidebarSpectrumAnalyzerSettingsStore.ledBars,
  showPeaks: sidebarSpectrumAnalyzerSettingsStore.showPeaks,
  trueLeds: sidebarSpectrumAnalyzerSettingsStore.trueLeds,
  barSpace: sidebarSpectrumAnalyzerSettingsStore.barSpace,
  showScaleX: false,
  showScaleY: false,
  channelLayout: sidebarSpectrumAnalyzerSettingsStore.channelLayout,
  colorMode: sidebarSpectrumAnalyzerSettingsStore.colorMode,
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

watch(() => sidebarSpectrumAnalyzerSettingsStore.mode, (newValue) => {
  if (analyzerInstance.value && (newValue == 10 || (newValue >= 0 && newValue <= 8))) {
    analyzerInstance.value.setOptions({ mode: newValue, barSpace: sidebarSpectrumAnalyzerSettingsStore.barSpace });
  }
});

watch(() => sidebarSpectrumAnalyzerSettingsStore.barSpace, (newValue) => {
  if (analyzerInstance.value && newValue >= 0 && newValue <= 1) {
    analyzerInstance.value.setOptions({ mode: sidebarSpectrumAnalyzerSettingsStore.mode, barSpace: newValue });
  }
});

watch(() => sidebarSpectrumAnalyzerSettingsStore.fps, (newValue) => {
  if (analyzerInstance.value && newValue >= 0 && newValue <= 144) {
    analyzerInstance.value.setOptions({ maxFPS: newValue });
  }
});

watch(() => sidebarSpectrumAnalyzerSettingsStore.height, (newValue) => {
  if (analyzerInstance.value && newValue > 0) {
    analyzerInstance.value.setOptions({ height: newValue });
  }
});

watch(() => sidebarSpectrumAnalyzerSettingsStore.channelLayout, (newValue) => {
  if (analyzerInstance.value) {
    analyzerInstance.value.setOptions({ channelLayout: newValue });
  }
});

watch(() => sidebarSpectrumAnalyzerSettingsStore.colorMode, (newValue) => {
  if (analyzerInstance.value) {
    analyzerInstance.value.setOptions({ colorMode: newValue });
  }
});

watch(() => sidebarSpectrumAnalyzerSettingsStore.gradient, (newValue) => {
  if (analyzerInstance.value) {
    analyzerInstance.value.setOptions({ gradient: newValue });
  }
});

watch(() => sidebarSpectrumAnalyzerSettingsStore.showPeaks, (newValue) => {
  if (analyzerInstance.value) {
    analyzerInstance.value.setOptions({ showPeaks: newValue });
  }
});

watch(() => sidebarSpectrumAnalyzerSettingsStore.ledBars, (newValue) => {
  if (analyzerInstance.value) {
    analyzerInstance.value.setOptions({ ledBars: newValue });
  }
});

watch(() => sidebarSpectrumAnalyzerSettingsStore.trueLeds, (newValue) => {
  if (analyzerInstance.value) {
    analyzerInstance.value.setOptions({ trueLeds: newValue });
  }
});

watch(() => sidebarSpectrumAnalyzerSettingsStore.loRes, (newValue) => {
  if (analyzerInstance.value) {
    analyzerInstance.value.setOptions({ loRes: newValue });
  }
});

const onToggleCurrentMode = () => {
  let mode = sidebarSpectrumAnalyzerSettingsStore.mode;
  if (++mode > 8) {
    mode = 1;
  }
  sidebarSpectrumAnalyzerSettingsStore.setMode(mode);
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
      analyzerInstance.value.gradient = sidebarSpectrumAnalyzerSettingsStore.gradient;
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
  if (playerStore.hasPreviousUserInteractions) {
    createAudioMotionAnalyzerInstance(defaultAnalyzerConstructorOptions, defaultAnalyzerOptions, true);
  }
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