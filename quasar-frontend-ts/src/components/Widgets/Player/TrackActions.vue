<template>
  <div id="current_track_actions">
    <q-btn-group spread>
      <q-btn dense unelevated size="md" :disable="disabled" :title="t('Toggle spectrum analyzer')"
        @click="onToggleAnalyzer"><q-icon name="bar_chart"
          :color="sidebarSpectrumAnalyzerSettingsStore.visible ? 'pink' : ''"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" :title="t('Toggle player shuffle mode')"><q-icon
          name="shuffle" :color="playerStore.shuffleMode ? 'pink' : ''" @click="onToggleShuffle"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" :title="repeatModeLabel" @click="onToggleRepeatMode"><q-icon
          :name="repeatModeIcon"
          :color="playerStore.repeatMode && playerStore.repeatMode != 'none' ? 'pink' : ''"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" :title="t('Toggle favorite track')"
        @click="onToggleFavorite"><q-icon name="favorite"
          :color="currentPlaylistItemStore.trackFavorited ? 'pink' : ''"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" :title="t('Download track')"
        v-if="currentPlaylistItemStore.trackDownloadURL" :href="currentPlaylistItemStore.trackDownloadURL"><q-icon
          name="file_download"></q-icon></q-btn>
      <q-btn dense unelevated size="md" disable :title="t('Download track')" v-else><q-icon
          name="file_download"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" :title="t('Toggle fullscreen visualization')"
        @click="onToggleVisualization"><q-icon name="screenshot_monitor"></q-icon></q-btn>
      <!-- TODO enable only for tracks-->
      <q-btn dense unelevated size="md" :title="t('View track details')" @click="onShowTrackDetailsModal"
        :disable="disabled"><q-icon name="tag"></q-icon></q-btn>
    </q-btn-group>
  </div>
</template>

<script setup lang="ts">
// TODO: translations
import { computed } from "vue";
import { useI18n } from "vue-i18n";
//import { bus } from "src/composables/bus";
import { useSidebarSpectrumAnalyzerSettingsStore } from "src/stores/sidebarSpectrumAnalyzerSettings";

import { useCurrentPlaylistItemStore } from 'src/stores/currentPlaylistItem';
import { usePlayerStore } from "src/stores/player";

const sidebarSpectrumAnalyzerSettingsStore = useSidebarSpectrumAnalyzerSettingsStore();

const currentPlaylistItemStore = useCurrentPlaylistItemStore();

const playerStore = usePlayerStore();

const { t } = useI18n();

defineProps({
  disabled: {
    type: Boolean,
    required: false,
    default: false
  }
});

const repeatModeIcon = computed(() => {
  let icon = null;
  switch (playerStore.repeatMode) {
    case 'track':
      icon = 'music_note';
      break;
    case 'playlist':
      icon = 'queue_music';
      break;
    default:
      icon = 'replay';
      break;
  }
  return (icon);
});

const repeatModeLabel = computed(() => {
  let label = null;
  switch (playerStore.repeatMode) {
    case 'track':
      label = 'Repeat mode: track';
      break;
    case 'playlist':
      label = 'Repeat mode: playlist';
      break;
    default:
      label = 'Repeat mode: none';
      break;
  }
  return (label);
});

function onToggleAnalyzer() {
  sidebarSpectrumAnalyzerSettingsStore.setVisibility(!sidebarSpectrumAnalyzerSettingsStore.visible);
}

function onToggleVisualization() {
  //bus.emit('showFullScreenVisualization');
}

function onToggleShuffle() {
  playerStore.toggleShuffeMode();
}

function onToggleRepeatMode() {
  playerStore.toggleRepeatMode();
}

async function onToggleFavorite() {
  try {
    await currentPlaylistItemStore.toggleFavoriteTrack();
  } catch (e: unknown) {
    console.error("Error toggling favorite", e);
  }
}

function onShowTrackDetailsModal() {
  //emit('toggleTrackDetailsModal');
}

</script>

<style lang="css">
div#current_track_actions {
  padding: 1rem;
}
</style>
