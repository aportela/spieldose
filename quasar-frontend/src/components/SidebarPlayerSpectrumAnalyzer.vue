<template>
  <div id="analyzer-container" class="cursor-pointer" :title="t('Toggle analyzer octave bands number')"
    @click="togglecurrentMode"></div>
</template>

<style scoped>
div#analyzer-container {
  width: 100%;
  height: 50px;
}
</style>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import AudioMotionAnalyzer from 'audiomotion-analyzer';
import { useI18n } from 'vue-i18n';
import { usePlayer } from 'stores/player';

const { t } = useI18n();

const props = defineProps({
  active: Boolean
});

const player = usePlayer();
const audioElement = ref(player.getElement);
const analyzer = ref(null);
const currentMode = ref(5);

function createAnalyzer() {
  const defaultOptions = {
    source: audioElement.value,
    start: false,
    height: 40,
    maxFPS: 30,
    mode: currentMode.value,
    ledBars: true,
    trueLeds: true,
    barSpace: 2,
    peakLine: true,
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
    document.getElementById('analyzer-container'),
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
  if (props.active) {
    analyzer.value.toggleAnalyzer();
  }
}

function togglecurrentMode() {
  if (++currentMode.value > 8) {
    currentMode.value = 1;
  }
  analyzer.value.setOptions({ mode: currentMode.value });
}

const active = computed(() => { return (props.active || false) });
watch(active, (newValue) => {
  if (newValue) {
    analyzer.value.start();
  } else {
    analyzer.value.stop();
  }
});

onMounted(() => {
  createAnalyzer();
});

</script>
