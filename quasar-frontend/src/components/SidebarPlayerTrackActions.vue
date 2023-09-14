<template>
  <div id="current_track_actions">
    <q-btn-group spread>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle navigation menu"><q-icon
          name="reorder"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle analyzer" @click="onToggleAnalyzer"><q-icon
          name="bar_chart"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Love/unlove track" @click="onToggleFavorite"><q-icon
          name="favorite" :color="favorited ? 'pink' : ''"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle random sort"><q-icon
          name="shuffle"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle repeat mode"><q-icon
          name="replay"></q-icon></q-btn>
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
import { useQuasar } from "quasar";
import { useI18n } from 'vue-i18n';
import { ref, watch } from 'vue';
import { useSessionStore } from "stores/session";
import { trackActions } from 'boot/spieldose';

const $q = useQuasar();
const { t } = useI18n();

const session = useSessionStore();

session.load();

const singleLayoutMode = ref(session.getSingleLayoutMode || false);

watch(singleLayoutMode, (newValue) => {
  session.saveSingleLayoutMode(newValue);
});

const props = defineProps({
  id: String,
  disabled: Boolean,
  favorited: Number,
  downloadURL: String
});

const emit = defineEmits(['toggleAnalyzer', 'toggleVisualization']);

function onToggleAnalyzer() {
  emit('toggleAnalyzer');
}

function onToggleVisualization() {
  emit('toggleVisualization');
}

function onToggleFavorite() {
  if (props.id) {
    const funct = !props.favorited ? trackActions.setFavorite : trackActions.unSetFavorite;
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
