<template>
  <div>
    <div class="q-pa-md q-gutter-sm text-center">
      <q-btn round dense size="md" :disable="disabled || !currentPlayListsStore.allowSkipPreviousItemOnActivePlayList"
        @click="onSkipPrevious">
        <q-icon name="skip_previous" title="Skip to previous track"></q-icon>
      </q-btn>
      <q-btn round dense size="lg" :disable="disabled" @click="onPlayPauseResume" class="q-mx-md">
        <q-icon :name="playPauseResumeIcon" title="Play/Pause/Resume track" :class="playPauseResumeClass"></q-icon>
      </q-btn>
      <q-btn round dense size="md" :disable="disabled || !currentPlayListsStore.allowSkipNextItemOnActivePlayList"
        @click="onSkipNext">
        <q-icon name="skip_next" title="Skip to next track"></q-icon>
      </q-btn>
    </div>
  </div>
</template>

<script setup lang="ts">

import { computed } from "vue";
import { useCurrentPlayListsStore } from "src/stores/currentPlayLists";

defineProps({
  disabled: {
    type: Boolean,
    required: false,
    default: false
  }
});

const currentPlayListsStore = useCurrentPlayListsStore();

const playPauseResumeClass = computed(() => currentPlayListsStore.playerIsPlaying || currentPlayListsStore.playerIsPaused ? 'text-pink-6' : '');
const playPauseResumeIcon = computed(() => currentPlayListsStore.playerIsPaused ? 'pause' : 'play_arrow');

const onSkipPrevious = () => {
  currentPlayListsStore.playerInteract();
  currentPlayListsStore.skipPreviousItemOnActivePlayList();
};

const onPlayPauseResume = () => {
  currentPlayListsStore.playerInteract();
  currentPlayListsStore.playerActionPlay(false)
};

const onSkipNext = () => {
  currentPlayListsStore.playerInteract();
  currentPlayListsStore.skipNextItemOnActivePlayList();
};
</script>