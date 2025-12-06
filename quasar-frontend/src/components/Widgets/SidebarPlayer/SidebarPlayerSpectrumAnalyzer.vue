<template>
  <div id="spieldose-sidebar-analyzer-container" class="cursor-pointer"
    :style="{ height: sidebarMiniSpectrumAnalyzerSettingsStore.currentHeight + 'px' }"
    :title="t('Toggle analyzer octave bands number')" @click="onToggleCurrentMode"></div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import AudioMotionAnalyzer from "audiomotion-analyzer";
import { usePlayerStore } from "src/stores/player";
import { useAudioMotionAnalyzerStore } from "src/stores/audioMotionAnalyzer";
import { useSidebarMiniSpectrumAnalyzerSettingsStore } from "src/stores/sidebarMiniSpectrumAnalyzerSettings";

const { t } = useI18n();

const playerStore = usePlayerStore();
const audioMotionAnalyzerStore = useAudioMotionAnalyzerStore();
const sidebarMiniSpectrumAnalyzerSettingsStore = useSidebarMiniSpectrumAnalyzerSettingsStore();

const analyzer = ref(null);

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

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.currentMode, (newValue) => {
  if (analyzer.value && (newValue == 10 || (newValue >= 0 && newValue <= 8))) {
    analyzer.value.setOptions({ mode: newValue, barSpace: sidebarMiniSpectrumAnalyzerSettingsStore.currentBarSpace });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.currentBarSpace, (newValue) => {
  if (analyzer.value && newValue >= 0 && newValue <= 1) {
    analyzer.value.setOptions({ mode: sidebarMiniSpectrumAnalyzerSettingsStore.currentMode, barSpace: newValue });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.currentFPS, (newValue) => {
  if (analyzer.value && newValue >= 0 && newValue <= 144) {
    analyzer.value.setOptions({ maxFPS: newValue });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.currentHeight, (newValue) => {
  if (analyzer.value && newValue > 0) {
    analyzer.value.setOptions({ height: newValue });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.currentChannelLayout, (newValue) => {
  if (analyzer.value) {
    analyzer.value.setOptions({ channelLayout: newValue });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.currentGradient, (newValue) => {
  if (analyzer.value) {
    analyzer.value.setOptions({ gradient: newValue });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.showPeaks, (newValue) => {
  if (analyzer.value) {
    analyzer.value.setOptions({ showPeaks: newValue });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.ledBarsActive, (newValue) => {
  if (analyzer.value) {
    analyzer.value.setOptions({ ledBars: newValue });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.trueLedsActive, (newValue) => {
  if (analyzer.value) {
    analyzer.value.setOptions({ trueLeds: newValue });
  }
});

watch(() => sidebarMiniSpectrumAnalyzerSettingsStore.isLoResActive, (newValue) => {
  if (analyzer.value) {
    analyzer.value.setOptions({ loRes: newValue });
  }
});

const onToggleCurrentMode = () => {
  let mode = sidebarMiniSpectrumAnalyzerSettingsStore.currentMode;
  if (++mode > 8) {
    mode = 1;
  }
  sidebarMiniSpectrumAnalyzerSettingsStore.setMode(mode);
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
    analyzer.value.gradient = sidebarMiniSpectrumAnalyzerSettingsStore.currentGradient;
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