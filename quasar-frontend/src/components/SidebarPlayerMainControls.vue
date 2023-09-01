<template>
  <div id="player_controls">
    <div class="q-pa-md q-gutter-sm text-center">
      <q-btn round dense size="md" :disable="disabled || ! allowSkipPrevious" @click="onSkipPrevious"><q-icon name="skip_previous"
          title="Skip to previous track"></q-icon></q-btn>
      <q-btn round dense size="lg" :disable="disabled || ! allowPlay" @click="onPlay" class="q-mx-md"><q-icon :name="isPlaying ? 'pause': 'play_arrow'"
          title="Play/Pause/Resume track" :class="{ 'text-pink-6': isPlaying }"></q-icon></q-btn>
      <q-btn round dense size="md" :disable="disabled || ! allowSkipNext" @click="onSkipNext"><q-icon name="skip_next"
          title="Skip to next track"></q-icon></q-btn>
    </div>
  </div>
</template>

<script setup>

const props = defineProps({
  disabled: Boolean,
  allowSkipPrevious: Boolean,
  allowPlay: Boolean,
  allowSkipNext: Boolean,
  isPlaying: Boolean
});

const emit = defineEmits(['skipPrevious', 'play', 'skipNext']);

function onPlay() {
  emit('play');
}

function onSkipPrevious() {
  emit('skipPrevious');
}

function onSkipNext() {
  emit('skipNext');
}
</script>
