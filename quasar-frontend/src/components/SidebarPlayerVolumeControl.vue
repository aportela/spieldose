<template>
  <q-list>
    <q-item>
      <q-item-section side>
        <q-icon class="cursor-pointer" :name="volumeIcon" @click.prevent="onToggleMute" :class="{ 'text-pink': muted }" />
      </q-item-section>
      <q-item-section>
        <q-slider :disable="disabled" v-model="volume" :min="0" :max="1" :step="0.05" label
          :label-value="volumePercentValue + '%'" @change="setVolume(volume)" />
      </q-item-section>
      <q-item-section side>
        {{ volumePercentValue }}%
      </q-item-section>
    </q-item>
  </q-list>
</template>

<script setup>

import { ref, computed } from "vue";

const props = defineProps({
  disabled: Boolean,
  defaultValue: Number
});

const emit = defineEmits(['volumeChange']);

const oldVolume = ref(0);
const volume = ref(props.defaultValue || 1);
const muted = ref(false);

const volumeIcon = computed(() => {
  if (volume.value == 0) {
    return ('volume_off');
  } else if (volume.value < 0.4) {
    return ('volume_mute');
  } else if (volume.value < 0.7) {
    return ('volume_down');
  } else {
    return ('volume_up');
  }
});

const volumePercentValue = computed(() => {
  return (Math.round(volume.value * 100));
});

function onToggleMute() {
  muted.value = !muted.value;
  if (muted.value) {
    oldVolume.value = volume.value;
    volume.value = 0;
  } else {
    volume.value = oldVolume.value;
  }
  emit('volumeChange', volume.value);
}

function setVolume(volume) {
  emit('volumeChange', volume);
}

</script>
