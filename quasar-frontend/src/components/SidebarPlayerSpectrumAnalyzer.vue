<template>
  <div id="spieldose-sidebar-analyzer-container" class="cursor-pointer" :title="t('Toggle analyzer octave bands number')"
    @click="togglecurrentMode"></div>
</template>

<style>
div#spieldose-sidebar-analyzer-container {
  width: 400px;
  height: 40px;
}
</style>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import AudioMotionAnalyzer from "audiomotion-analyzer";
import { useSpieldoseStore } from "stores/spieldose";

const props = defineProps({
  active: Boolean,
  mode: Number
});

const emit = defineEmits(['change']);

const { t } = useI18n();
const spieldoseStore = useSpieldoseStore();
const currentMode = ref(props.mode || 7);
const analyzer = ref(null);

const active = computed(() => { return (props.active || false) });

watch(active, (newValue) => {
  if (analyzer.value) {
    if (newValue) {
      analyzer.value.start();
    } else {
      analyzer.value.stop();
    }
  }
});

function createAnalyzer() {
  const defaultOptions = {
    source: spieldoseStore.getAudioInstance,
    start: false,
    width: 400,
    height: 40,
    maxFPS: 30,
    mode: currentMode.value,
    ledBars: true,
    showPeaks: true,
    trueLeds: false,
    barSpace: 0.2,
    showScaleX: false,
    showScaleY: false,
    channelLayout: 'single',
    colorcurrentMode: 'gradient',
    splitGradient: false,
    bgAlpha: 1,
    overlay: true,
    showBgColor: true
  };
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
  analyzer.value.registerGradient('default-spieldose', gradientOptions);
  analyzer.value.gradient = 'default-spieldose';
  spieldoseStore.setAudioMotionAnalyzerSource(analyzer.value.connectedSources[0]);
  if (props.active) {
    analyzer.value.start();
  }
}

function togglecurrentMode() {
  if (++currentMode.value > 8) {
    currentMode.value = 1;
  }
  if (analyzer.value) {
    analyzer.value.setOptions({ mode: currentMode.value, barSpace: (9 - currentMode.value) / 10 });
  }
  emit('change', { mode: currentMode.value });
}

onMounted(() => {
  createAnalyzer();
});

</script>
