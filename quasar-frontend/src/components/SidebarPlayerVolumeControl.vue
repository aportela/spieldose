<template>
  <q-list>
    <q-item>
      <q-item-section side>
        <q-icon :name="volumeIcon" />
      </q-item-section>
      <q-item-section>
        <q-slider v-model="volume" :min="0" :max="10" label />
      </q-item-section>
      <q-item-section side>
        {{ volume * 10 }}%
      </q-item-section>
    </q-item>
  </q-list>
</template>

<script setup>
import { ref, computed } from "vue";

const volume = ref(8);

const volumeIcon = computed(() => {
  if (volume.value == 0) {
    return ('volume_off');
  } else if (volume.value < 4) {
    return ('volume_mute');
  } else if (volume.value < 7) {
    return ('volume_down');
  } else {
    return ('volume_up');
  }
});
</script>
