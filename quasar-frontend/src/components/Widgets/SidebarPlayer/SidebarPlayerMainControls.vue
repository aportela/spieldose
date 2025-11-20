<template>
  <div>
    <div class="q-pa-md q-gutter-sm text-center">
      <q-btn round dense size="md" :disable="disabled || !playerStore.allowSkipPrevious" @click="onSkipPrevious">
        <q-icon name="skip_previous" title="Skip to previous track"></q-icon>
      </q-btn>
      <q-btn round dense size="lg" :disable="disabled" @click="onTogglePlayPauseResume" class="q-mx-md">
        <q-icon :name="playPauseResumeIcon" title="Play/Pause/Resume track" :class="playPauseResumeClass"></q-icon>
      </q-btn>
      <q-btn round dense size="md" :disable="disabled || !playerStore.allowSkipNext" @click="onSkipNext">
        <q-icon name="skip_next" title="Skip to next track"></q-icon>
      </q-btn>
    </div>
  </div>
</template>

<script setup>

import { computed } from "vue";
import { usePlayerStore } from 'src/stores/player';

const props = defineProps({
  disabled: {
    type: Boolean,
    required: false,
    default: false
  }
});

const playerStore = usePlayerStore();


const playPauseResumeClass = computed(() => playerStore.status == 'playing' || playerStore.status == 'paused' ? 'text-pink-6' : '');

const playPauseResumeIcon = computed(() => playerStore.status == 'paused' ? 'pause' : 'play_arrow');

function onTogglePlayPauseResume() {
  playerStore.interact();
  playerStore.play();
}

function onSkipPrevious() {
  playerStore.interact();
  // TODO
}

function onSkipNext() {
  playerStore.interact();
  // TODO
}

</script>
