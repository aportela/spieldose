<template>
  <div id="current_track_actions">
    <q-btn-group spread>
      <q-btn dense unelevated size="md" :disable="disabled" :title="t('Toggle spectrum analyzer')"
        @click="onToggleAnalyzer"><q-icon name="bar_chart"
          :color="sidebarSpectrumAnalyzerSettingsStore.visible ? 'pink' : ''"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" :title="t('Toggle player shuffle mode')"><q-icon
          name="shuffle" :color="currentPlayListsStore.playerShuffle ? 'pink' : ''"
          @click="onToggleShuffle"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" :title="repeatModeLabel" @click="onToggleRepeatMode"><q-icon
          :name="repeatModeIcon"
          :color="currentPlayListsStore.playerRepeatMode !== 'none' ? 'pink' : ''"></q-icon></q-btn>
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
import { useCurrentPlayListsStore } from "src/stores/currentPlayLists";

const sidebarSpectrumAnalyzerSettingsStore = useSidebarSpectrumAnalyzerSettingsStore();

const currentPlaylistItemStore = useCurrentPlaylistItemStore();
const currentPlayListsStore = useCurrentPlayListsStore();

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
  switch (currentPlayListsStore.playerRepeatMode) {
    case 'track':
      icon = 'music_note';
      break;
    case 'playList':
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
  switch (currentPlayListsStore.playerRepeatMode) {
    case 'track':
      label = 'Repeat mode: track';
      break;
    case 'playList':
      label = 'Repeat mode: playlist';
      break;
    default:
      label = 'Repeat mode: none';
      break;
  }
  return (label);
});

function onToggleAnalyzer() {
  sidebarSpectrumAnalyzerSettingsStore.setVisible(!sidebarSpectrumAnalyzerSettingsStore.visible);
}

function onToggleVisualization() {
  //bus.emit('showFullScreenVisualization');
}

function onToggleShuffle() {
  currentPlayListsStore.togglePlayerShuffeMode();
}

function onToggleRepeatMode() {
  currentPlayListsStore.togglePlayerRepeatMode();
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