<template>
  <q-list>
    <q-item>
      <q-item-section side>
        {{ audioCurrentTimeLabel }}
      </q-item-section>
      <q-item-section>
        <q-slider :disable="disabled" v-model="currentTime" :min="0" :max="playerStore.audioDuration" :step="1" label
          :label-value="audioCurrentTimeLabel" @change="onSeek" @update:model-value="playerStore.seek" />
      </q-item-section>
      <q-item-section side>{{ audioDurationLabel }}</q-item-section>
    </q-item>
  </q-list>
</template>


<script setup>
import { ref, watch, computed } from "vue";
import { usePlayerStore } from "src/stores/player";

const playerStore = usePlayerStore();

const props = defineProps({
  disabled: Boolean,
});

const audioCurrentTimeLabel = computed(() => formatSecondsAsTime(Math.floor(playerStore.audioCurrentTime)));
const audioDurationLabel = computed(() => formatSecondsAsTime(Math.floor(playerStore.audioDuration)));

const currentTime = ref(Math.floor(playerStore.audioCurrentTime));

watch(() => playerStore.audioCurrentTime, (newValue) => {
  currentTime.value = Math.floor(playerStore.audioCurrentTime);
});

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