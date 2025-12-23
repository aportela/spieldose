<template>
  <div>
    <div class="q-pa-md q-gutter-sm text-center">
      <q-btn round dense size="md" :disable="disabled || !currentPlayListsStore.allowSkipPreviousItemOnActivePlayList"
        @click="onSkipPrevious">
        <q-icon name="skip_previous" :title="t('Skip to previous track')" />
      </q-btn>
      <q-btn round dense size="lg" :disable="disabled" @click="onPlayPauseResume" class="q-mx-md">
        <q-icon :name="playPauseResumeIcon" :title="t('Play/Pause/Resume track')" :class="playPauseResumeClass" />
      </q-btn>
      <q-btn round dense size="md" :disable="disabled || !currentPlayListsStore.allowSkipNextItemOnActivePlayList"
        @click="onSkipNext">
        <q-icon name="skip_next" :title="t('Skip to next track')" />
      </q-btn>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from "vue";
  import { useCurrentPlayListsStore } from "src/stores/currentPlayLists";
  import { useI18n } from "vue-i18n";

  const { t } = useI18n();

  interface MainControlsProps {
    disabled?: boolean;
  }

  withDefaults(defineProps<MainControlsProps>(), {
    disabled: false,
  });

  const currentPlayListsStore = useCurrentPlayListsStore();

  const playPauseResumeClass = computed(() => currentPlayListsStore.playerIsPlaying || currentPlayListsStore.playerIsPaused ? 'text-pink-6' : '');
  const playPauseResumeIcon = computed(() => currentPlayListsStore.playerIsPaused ? 'pause' : 'play_arrow');

  const onSkipPrevious = async () => {
    currentPlayListsStore.playerInteract();
    try {
      await currentPlayListsStore.skipPreviousItemOnActivePlayList();
    } catch (e) {
      console.error(e);
    }
  };

  const onPlayPauseResume = () => {
    currentPlayListsStore.playerInteract();
    currentPlayListsStore.playerActionPlay(false)
  };

  const onSkipNext = async () => {
    currentPlayListsStore.playerInteract();
    try {
      await currentPlayListsStore.skipNextItemOnActivePlayList();
    } catch (e) {
      console.error(e);
    }
  };
</script>
