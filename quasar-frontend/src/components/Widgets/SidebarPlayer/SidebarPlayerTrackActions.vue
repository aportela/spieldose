<template>
  <div id="current_track_actions">
    <q-btn-group spread>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle analyzer" @click="onToggleAnalyzer"><q-icon
          name="bar_chart" :color="miniSpectrumAnalyzerSettings.isVisible ? 'pink' : ''"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle shuffle"><q-icon name="shuffle"
          :color="playerStore.shuffleMode ? 'pink' : ''" @click="playerStore.toggleShuffeMode"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" :title="repeatModeLabel"
        @click="playerStore.toggleRepeatMode"><q-icon :name="repeatModeIcon"
          :color="playerStore.repeatMode && playerStore.repeatMode != 'none' ? 'pink' : ''"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle favorite track"
        @click="onToggleFavorite"><q-icon name="favorite"
          :color="currentPlaylistItemStore.trackFavorited ? 'pink' : ''"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Download track"
        v-if="currentPlaylistItemStore.trackDownloadURL" :href="currentPlaylistItemStore.trackDownloadURL"><q-icon
          name="file_download"></q-icon></q-btn>
      <q-btn dense unelevated size="md" disable title="Download track" v-else><q-icon
          name="file_download"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle visualization"
        @click="onToggleVisualization"><q-icon name="screenshot_monitor"></q-icon></q-btn>
      <!-- TODO enable only for tracks-->
      <q-btn dense unelevated size="md" title="View track details & lyrics" @click="onShowTrackDetailsModal"
        :disable="disabled"><q-icon name="tag"></q-icon></q-btn>
    </q-btn-group>
  </div>
</template>

<script setup>
// TODO: translations
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { useBus } from "src/composables/useBus";
import { useMiniSpectrumAnalyzerSettingsStore } from "src/stores/miniSpectrumAnalyzerSettings";

import { useCurrentPlaylistItemStore } from 'src/stores/currentPlaylistItem';
import { usePlayerStore } from "src/stores/player";

const miniSpectrumAnalyzerSettings = useMiniSpectrumAnalyzerSettingsStore();

const currentPlaylistItemStore = useCurrentPlaylistItemStore();

const playerStore = usePlayerStore();

const { bus } = useBus();

const { t } = useI18n();

const props = defineProps({
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
  miniSpectrumAnalyzerSettings.setVisibility(!miniSpectrumAnalyzerSettings.isVisible);
}

function onToggleVisualization() {
  //bus.emit('showFullScreenVisualization');
}

function onToggleShuffle() {
  //emit('toggleShuffle');
}

function onToggleRepeatMode() {
  //emit('toggleRepeatMode');
}

function onToggleFavorite() {
  currentPlaylistItemStore.toggleFavoriteTrack();
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