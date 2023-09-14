<template>
  <div id="current_track_actions">
    <q-btn-group spread>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle analyzer" @click="onToggleAnalyzer"><q-icon
          name="bar_chart" :color="visibleAnalyzer ? 'pink' : ''"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle shuffle"><q-icon name="shuffle"
          :color="shuffle ? 'pink' : ''" @click="onToggleShuffle"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" :title="repeatModeLabel" @click="onToggleRepeatMode"><q-icon
          :name="repeatModeIcon" :color="repeatMode ? 'pink' : ''"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Love/unlove track" @click="onToggleFavorite"><q-icon
          name="favorite" :color="isTrackFavorited ? 'pink' : ''"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Download track" v-if="downloadURL"
        :href="downloadURL"><q-icon name="file_download"></q-icon></q-btn>
      <q-btn dense unelevated size="md" disable title="Download track" v-else><q-icon
          name="file_download"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle visualization"
        @click="onToggleVisualization"><q-icon name="screenshot_monitor"></q-icon></q-btn>
      <!-- TODO enable only for tracks-->
      <q-btn dense unelevated size="md" :disable="disabled" title="Track tags details"
        @click="onShowTrackDetailsModal"><q-icon name="tag"></q-icon></q-btn>
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
import { useQuasar } from 'quasar';
import { useI18n } from 'vue-i18n';
import { trackActions } from 'boot/spieldose';

const $q = useQuasar();
const { t } = useI18n();

const props = defineProps({
  id: String,
  disabled: Boolean,
  visibleAnalyzer: Boolean,
  shuffle: Boolean,
  repeatMode: String,
  isTrackFavorited: Number,
  downloadURL: String
});

const emit = defineEmits(['toggleAnalyzer', 'toggleVisualization', 'toggleShuffle', 'toggleRepeatMode']);

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
  emit('toggleVisualization');
}

function onToggleShuffle() {
  emit('toggleShuffle');
}

function onToggleRepeatMode() {
  emit('toggleRepeatMode');
}

function onToggleFavorite() {
  if (props.id) {
    const funct = !props.isTrackFavorited ? trackActions.setFavorite : trackActions.unSetFavorite;
    funct(props.id).then((success) => {
    })
      .catch((error) => {
        switch (error.response.status) {
          default:
            // TODO: custom message
            $q.notify({
              type: "negative",
              message: t("API Error: error when toggling favorite flag"),
              caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
            });
            break;
        }
      });
  }
}

function onShowTrackDetailsModal() {
  emit('toggleTrackDetailsModal');
}

</script>
