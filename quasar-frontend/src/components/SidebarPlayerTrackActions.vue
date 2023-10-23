<template>
  <div id="current_track_actions">
    <q-btn-group spread>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle analyzer" @click="onToggleAnalyzer"><q-icon
          name="bar_chart" :color="visibleAnalyzer ? 'pink' : ''"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle shuffle"><q-icon name="shuffle"
          :color="shuffle ? 'pink' : ''" @click="onToggleShuffle"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" :title="repeatModeLabel" @click="onToggleRepeatMode"><q-icon
          :name="repeatModeIcon" :color="repeatMode && repeatMode != 'none' ? 'pink' : ''"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle favorite track"
        @click="onToggleFavorite"><q-icon name="favorite" :color="trackFavoritedTimestamp ? 'pink' : ''"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Download track" v-if="downloadURL"
        :href="downloadURL"><q-icon name="file_download"></q-icon></q-btn>
      <q-btn dense unelevated size="md" disable title="Download track" v-else><q-icon
          name="file_download"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle visualization"
        @click="onToggleVisualization"><q-icon name="screenshot_monitor"></q-icon></q-btn>
      <!-- TODO enable only for tracks-->
      <q-btn dense unelevated size="md" title="View track details & lyrics" @click="onShowTrackDetailsModal" :disable="disabled"><q-icon
          name="tag"></q-icon></q-btn>
    </q-btn-group>
  </div>
</template>

<style>
div#current_track_actions {
  padding: 1rem;
}
</style>


<script setup>
// TODO: translations
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { bus } from "boot/bus";

const { t } = useI18n();

const props = defineProps({
  id: String,
  disabled: Boolean,
  visibleAnalyzer: Boolean,
  shuffle: Boolean,
  repeatMode: String,
  trackFavoritedTimestamp: Number,
  downloadURL: String
});



const emit = defineEmits(['toggleAnalyzer', 'toggleVisualization', 'toggleShuffle', 'toggleRepeatMode', 'toggleFavorite', 'toggleTrackDetailsModal']);

const repeatModeIcon = computed(() => {
  let icon = null;
  switch (props.repeatMode) {
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
  switch (props.repeatMode) {
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
  emit('toggleAnalyzer');
}

function onToggleVisualization() {
  bus.emit('showFullScreenVisualization');
}

function onToggleShuffle() {
  emit('toggleShuffle');
}

function onToggleRepeatMode() {
  emit('toggleRepeatMode');
}

function onToggleFavorite() {
  emit('toggleFavorite');
}

function onShowTrackDetailsModal() {
  emit('toggleTrackDetailsModal');
}

</script>
