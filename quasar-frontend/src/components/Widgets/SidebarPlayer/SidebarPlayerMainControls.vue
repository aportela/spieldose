<template>
  <div>
    <div class="q-pa-md q-gutter-sm text-center">
      <q-btn round dense size="md" :disable="disabled || !playerStore.allowSkipPrevious" @click="skipPrevious">
        <q-icon name="skip_previous" title="Skip to previous track"></q-icon>
      </q-btn>
      <q-btn round dense size="lg" :disable="disabled" @click="togglePlay" class="q-mx-md">
        <q-icon :name="playPauseResumeIcon" title="Play/Pause/Resume track" :class="playPauseResumeClass"></q-icon>
      </q-btn>
      <q-btn round dense size="md" :disable="disabled || !playerStore.allowSkipNext" @click="skipNext">
        <q-icon name="skip_next" title="Skip to next track"></q-icon>
      </q-btn>
    </div>
  </div>
</template>

<script setup>

import { computed } from "vue";
import { usePlayerStore } from 'src/stores/player';
import { usePlayerActions } from "src/composables/usePlayerActions";

const props = defineProps({
  disabled: {
    type: Boolean,
    required: false,
    default: false
  }
});

const playerStore = usePlayerStore();

const { togglePlay, skipPrevious, skipNext } = usePlayerActions();

const playPauseResumeClass = computed(() => playerStore.status == 'playing' || playerStore.status == 'paused' ? 'text-pink-6' : '');
const playPauseResumeIcon = computed(() => playerStore.status == 'paused' ? 'pause' : 'play_arrow');

</script>
