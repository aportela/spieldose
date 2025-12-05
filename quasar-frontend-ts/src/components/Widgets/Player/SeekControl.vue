<template>
  <q-list>
    <q-item>
      <q-item-section side>
        {{ audioCurrentTimeLabel }}
      </q-item-section>
      <q-item-section>
        <q-slider :disable="disabled" v-model="currentTime" :min="0" :max="playerStore.audioDuration" :step="1" label
          :label-value="audioCurrentTimeLabel" @update:model-value="playerStore.seek" />
      </q-item-section>
      <q-item-section side>{{ audioDurationLabel }}</q-item-section>
    </q-item>
  </q-list>
</template>


<script setup lang="ts">
import { ref, watch, computed } from "vue";
import { usePlayerStore } from "src/stores/player";

const playerStore = usePlayerStore();

const props = defineProps({
  disabled: {
    type: Boolean,
    required: false,
    default: false
  }
});

const audioCurrentTimeLabel = computed(() => formatSecondsAsTime(Math.floor(playerStore.audioCurrentTime)));
const audioDurationLabel = computed(() => formatSecondsAsTime(Math.floor(playerStore.audioDuration)));

const currentTime = ref(Math.floor(playerStore.audioCurrentTime));

watch(() => playerStore.audioCurrentTime, (newValue) => {
  currentTime.value = Math.floor(playerStore.audioCurrentTime);
});

const formatSecondsAsTime = (seconds: number): string => {
  if (Number.isInteger(seconds) && seconds >= 0) {
    const hoursValue = Math.floor(seconds / 3600);
    let minutesValue = Math.floor((seconds - (hoursValue * 3600)) / 60);
    let secondsValue = Math.floor(seconds - (hoursValue * 3600) - (minutesValue * 60));
    const minutesStr: string = minutesValue < 10 ? '0' + minutesValue : minutesValue.toString();
    const secondsStr: string = secondsValue < 10 ? '0' + secondsValue : secondsValue.toString();
    return `${minutesStr}:${secondsStr}`;
  } else {
    return '00:00';
  }
};

</script>