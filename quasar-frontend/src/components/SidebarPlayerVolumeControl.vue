<template>
  <q-list>
    <q-item>
      <q-item-section side>
        <q-icon class="cursor-pointer" :name="volumeIcon" @click.stop="onToggleMute"
          :class="{ 'text-pink': isMuted }" />
      </q-item-section>
      <q-item-section>
        <q-slider v-model="volume" :min="0" :max="1" :step="0.05" label :label-value="volumePercentValue + '%'"
          @change="setVolume(volume)" />
      </q-item-section>
      <q-item-section side>
        {{ volumePercentValue }}%
      </q-item-section>
    </q-item>
  </q-list>
</template>

<script setup>

import { ref, watch, computed } from "vue";

const props = defineProps({
  defaultValue: Number,
  isMuted: Boolean
});

const emit = defineEmits(['volumeChange', 'toggleMute']);

const volume = ref(props.defaultValue || 1);

const volumeIcon = computed(() => {
  if (volume.value == 0 || props.isMuted) {
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
  emit('toggleMute');
}

function setVolume(volume) {
  emit('volumeChange', volume);
}

</script>
