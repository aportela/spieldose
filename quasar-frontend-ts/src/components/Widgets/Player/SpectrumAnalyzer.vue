<template>
  <div id="spieldose-sidebar-analyzer-container" class="cursor-pointer"
    :style="{ height: miniSpectrumAnalyzerSettingsStore.currentHeight + 'px' }"
    :title="t('Toggle analyzer octave bands number')" @click="onToggleCurrentMode"></div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import AudioMotionAnalyzer from "audiomotion-analyzer";
import { usePlayerStore } from "src/stores/player";
import { useAudioMotionAnalyzerStore } from "src/stores/audioMotionAnalyzer";
import { useMiniSpectrumAnalyzerSettingsStore } from "src/stores/miniSpectrumAnalyzerSettings";

const { t } = useI18n();

const playerStore = usePlayerStore();
const audioMotionAnalyzerStore = useAudioMotionAnalyzerStore();
const miniSpectrumAnalyzerSettingsStore = useMiniSpectrumAnalyzerSettingsStore();

const analyzer = ref(null);

const defaultAnalyzerOptions = {
  showCanvas: true,
  source: audioMotionAnalyzerStore.audioInstance,
  connectSpeakers: audioMotionAnalyzerStore.connectSpeakers,
  start: false,
  loRes: miniSpectrumAnalyzerSettingsStore.isLoResActive,
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
    }
  }
});

watch(() => miniSpectrumAnalyzerSettingsStore.currentMode, (newValue) => {
  if (analyzer.value && (newValue == 10 || (newValue >= 0 && newValue <= 8))) {
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

watch(() => miniSpectrumAnalyzerSettingsStore.isLoResActive, (newValue) => {
  if (analyzer.value) {
    analyzer.value.setOptions({ loRes: newValue });
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
    if (!audioMotionAnalyzerStore.hasOtherRuningInstances) {
      audioMotionAnalyzerStore.instance();
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


onMounted(() => {
  // TODO: WARNING: on empty playlists js console show warning about AudioContext auto start denied
  createAudioMotionAnalyzer(defaultAnalyzerOptions, playerStore.hasPreviousUserInteractions);
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

canvas {
  display: block;
  width: 100%;
}
</style>