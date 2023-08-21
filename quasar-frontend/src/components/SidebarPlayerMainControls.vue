<template>
  <div id="player_controls">
    <div class="q-pa-md q-gutter-sm text-center">
      <q-btn round dense size="md" :disable="disabled || ! allowSkipPrevious" @click="onSkipPrevious"><q-icon name="skip_previous" class="cursor-pointer"
          title="Toggle navigation menu"></q-icon></q-btn>
      <q-btn round dense size="lg" :disable="disabled || ! allowPlay" @click="onPlay" class="q-mx-md"><q-icon name="play_arrow" class="cursor-pointer"
          title="Toggle navigation menu"></q-icon></q-btn>
      <q-btn round dense size="md" :disable="disabled || ! allowSkipNext" @click="onSkipNext"><q-icon name="skip_next" class="cursor-pointer"
          title="Toggle navigation menu"></q-icon></q-btn>
    </div>
  </div>
</template>

<script setup>

const props = defineProps({
  disabled: Boolean,
  allowSkipPrevious: Boolean,
  allowPlay: Boolean,
  allowSkipNext: Boolean
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
