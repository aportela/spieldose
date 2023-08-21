<template>
  <q-list>
    <q-item>
      <q-item-section side>
        00:00
      </q-item-section>
      <q-item-section>
        <q-slider v-model="currentTime" :min="0" :max="1" :step="0.01" label
          :label-value="currentTrackTimeData.currentTime" @change="onSeek"/>
      </q-item-section>
      <q-item-section side>
        {{ currentTrackTimeData.duration }}
      </q-item-section>
    </q-item>
  </q-list>
</template>


<script setup>
import { ref, watch, computed } from "vue";

const props = defineProps({
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

</script>
