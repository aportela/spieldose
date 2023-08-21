<template>
  <q-list>
    <q-item>
      <q-item-section side>
        <q-icon class="cursor-pointer" :name="volumeIcon" @click.prevent="onToggleMute" :class="{ 'text-pink': muted }"/>
      </q-item-section>
      <q-item-section>
        <q-slider :disable="disabled" v-model="volume" :min="0" :max="1" :step="0.05" label :label-value="volumePercentValue + '%'" />
      </q-item-section>
      <q-item-section side>
        {{ volumePercentValue }}%
      </q-item-section>
    </q-item>
  </q-list>
</template>

<script setup>
import { ref, computed, watch } from "vue";

const props = defineProps({
  disabled: Boolean
});

const oldVolume = ref(0);
const volume = ref(0.8);
const muted = ref(false);

watch(volume, (newValue) => {
  setVolume(newValue);
});

watch(muted, (newValue) => {
  if (muted.value) {
    oldVolume.value = volume.value;
    volume.value = 0;
  } else {
    volume.value = oldVolume.value;
  }
});

const emit = defineEmits(['volumeChange']);

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
  return(Math.round(volume.value * 100));
});

function onToggleMute() {
  muted.value = ! muted.value;
}

function setVolume(volume) {
  emit('volumeChange', volume);
  /*
            if (volume >= 0 && volume <= 1) {
                if (this.audioElement) {
                    this.audioElement.volume = volume;
                    this.$localStorage.set('volume', volume);
                } else {
                    console.error("Audio element not mounted");
                }
            } else {
                console.error("Error setting volume, invalid value:" + volume);
            }
            */
}

</script>
