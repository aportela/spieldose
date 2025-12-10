<template>
  <div>
    <div class="q-pa-md q-gutter-sm text-center">
      <q-btn round dense size="md" :disable="disabled || !currentPlayListsStore.allowSkipPreviousItemOnActivePlayList"
        @click="currentPlayListsStore.skipPreviousItemOnActivePlayList">
        <q-icon name="skip_previous" title="Skip to previous track"></q-icon>
      </q-btn>
      <q-btn round dense size="lg" :disable="disabled" @click="togglePlay" class="q-mx-md">
        <q-icon :name="playPauseResumeIcon" title="Play/Pause/Resume track" :class="playPauseResumeClass"></q-icon>
      </q-btn>
      <q-btn round dense size="md" :disable="disabled || !currentPlayListsStore.allowSkipNextItemOnActivePlayList"
        @click="currentPlayListsStore.skipNextItemOnActivePlayList">
        <q-icon name="skip_next" title="Skip to next track"></q-icon>
      </q-btn>
    </div>
  </div>
</template>

<script setup lang="ts">

import { computed } from "vue";
import { usePlayerStore } from 'src/stores/player';
import { useCurrentPlayListsStore } from "src/stores/currentPlayLists";
import { togglePlay, skipPrevious, skipNext } from "src/composables/playerActions";

defineProps({
  disabled: {
    type: Boolean,
    required: false,
    default: false
  }
});

const playerStore = usePlayerStore();
const currentPlayListsStore = useCurrentPlayListsStore();

const playPauseResumeClass = computed(() => playerStore.status == 'playing' || playerStore.status == 'paused' ? 'text-pink-6' : '');
const playPauseResumeIcon = computed(() => playerStore.status == 'paused' ? 'pause' : 'play_arrow');

</script>