<template>
  <div id="spieldose-sidebar-analyzer-container" class="cursor-pointer"
    :title="t('Toggle analyzer octave bands number')" @click="togglecurrentMode"></div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import AudioMotionAnalyzer from "audiomotion-analyzer";
import { usePlayerStore } from "src/stores/player";
import { useLocalStorage } from "src/composables/useLocalStorage";

const props = defineProps({
  mode: Number
});

//const emit = defineEmits(['change']);

const { t } = useI18n();

const playerStore = usePlayerStore();

const localStorage = useLocalStorage();

const currentMode = ref(localStorage.playerMiniAnalyzerMode.get());
const analyzer = ref(null);

const active = ref(true); // computed(() => { return (props.active || false) });


/*
watch(playerStore.hasPreviousUserInteractions, (newValue, oldValue) => {
  if (!oldValue && newValue && !analyzer.value) {
    createAnalyzer(props.active);
  }
});

*/
/*
watch(active, (newValue) => {
  if (analyzer.value) {
    if (newValue) {
      analyzer.value.start();
    } else {
      analyzer.value.stop();
    }
  }
});
*/

function createAnalyzer(start) {
  const defaultOptions = {
    source: playerStore.audioInstance,
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
  playerStore.setAudioMotionAnalyzerSource(analyzer.value.connectedSources[0]);
  if (start) {
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
  localStorage.playerMiniAnalyzerMode.set(currentMode.value)
}

onMounted(() => {
  // TODO: WARNING: on empty playlists js console show warning about AudioContext auto start denied
  if (playerStore.hasPreviousUserInteractions) {
    createAnalyzer(active.value);
  }
});

</script>

<style lang="css">
div#spieldose-sidebar-analyzer-container {
  width: 400px;
  height: 40px;
}
</style>
