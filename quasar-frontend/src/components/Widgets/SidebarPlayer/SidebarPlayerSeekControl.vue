<template>
  <q-list>
    <q-item>
      <q-item-section side>
        {{ formatSecondsAsTime(currentElementTimeData.currentTime) }}
      </q-item-section>
      <q-item-section>
        <q-slider :disable="disabled" v-model="currentTime" :min="0" :max="1" :step="0.01" label
          :label-value="formatSecondsAsTime(currentElementTimeData.currentTime)" @change="onSeek" />
      </q-item-section>
      <q-item-section side>
        {{ formatSecondsAsTime(currentElementTimeData.duration) }}
      </q-item-section>
    </q-item>
  </q-list>
</template>


<script setup>
import { ref, reactive, watch, computed } from "vue";
import { usePlayerStore } from "src/stores/player";

const playerStore = usePlayerStore();

const currentElementTimeData = reactive({
  duration: 0,
  currentTime: 0,
  currentProgress: 0,
  position: 0,
});

const audioElement = ref(playerStore.audioInstance);

audioElement.value.addEventListener('timeupdate', (event) => {
  currentElementTimeData.currentProgress = audioElement.value.currentTime / audioElement.value.duration;
  currentElementTimeData.duration = Math.floor(audioElement.value.duration);
  currentElementTimeData.currentTime = Math.floor(audioElement.value.currentTime);
  if (!isNaN(currentElementTimeData.currentProgress)) {
    currentElementTimeData.position = Number(currentElementTimeData.currentProgress.toFixed(2));
  } else {
    currentElementTimeData.position = 0;
  }
});

const props = defineProps({
  disabled: Boolean,
});

const position = computed(() => {
  return (currentElementTimeData.position);
});

watch(position, (newValue) => {
  currentTime.value = parseFloat(newValue);
});

const currentTime = ref(0);

function onSeek() {
  playerStore.setCurrentTime(currentTime.value * playerStore.duration);
};

function formatSecondsAsTime(secs, format) {
  if (secs && Number.isInteger(secs) && secs > 0) {
    var hr = Math.floor(secs / 3600);
    var min = Math.floor((secs - (hr * 3600)) / 60);
    var sec = Math.floor(secs - (hr * 3600) - (min * 60));

    if (min < 10) {
      min = '0' + min;
    }
    if (sec < 10) {
      sec = '0' + sec;
    }
    return (min + ':' + sec);
  } else {
    return ('00:00');
  }
}

</script>
