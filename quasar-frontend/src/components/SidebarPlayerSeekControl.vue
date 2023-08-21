<template>
  <q-list>
    <q-item>
      <q-item-section side>
        {{ formatSecondsAsTime(currentTrackTimeData.currentTime) }}
      </q-item-section>
      <q-item-section>
        <q-slider :disable="disabled" v-model="currentTime" :min="0" :max="1" :step="0.01" label
          :label-value="formatSecondsAsTime(currentTrackTimeData.currentTime)" @change="onSeek"/>
      </q-item-section>
      <q-item-section side>
        {{ formatSecondsAsTime(currentTrackTimeData.duration) }}
      </q-item-section>
    </q-item>
  </q-list>
</template>


<script setup>
import { ref, watch, computed } from "vue";

const props = defineProps({
  disabled: Boolean,
  currentTrackTimeData: Object
});

const emit = defineEmits(['seek']);

const position = computed(() => {
  return (props.currentTrackTimeData.position);
});


watch(position, (newValue) => {
  currentTime.value = parseFloat(newValue);
});

const currentTime = ref(0);

function onSeek() {
  emit('seek', currentTime.value);
};

function formatSecondsAsTime(secs, format) {
  var hr = Math.floor(secs / 3600);
  var min = Math.floor((secs - (hr * 3600)) / 60);
  var sec = Math.floor(secs - (hr * 3600) - (min * 60));

  if (min < 10) {
    min = "0" + min;
  }
  if (sec < 10) {
    sec = "0" + sec;
  }

  return min + ':' + sec;
}

</script>
