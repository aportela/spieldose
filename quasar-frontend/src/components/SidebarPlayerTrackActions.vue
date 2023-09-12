<template>
  <div id="current_track_actions">
    <q-btn-group spread>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle navigation menu"><q-icon
          name="reorder"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle analyzer"><q-icon name="bar_chart"
          @click="onToggleAnalyzer"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Love/unlove track"><q-icon name="favorite"
          :color="favorited ? 'pink' : ''" @click="onToggleFavorite"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle random sort"><q-icon
          name="shuffle"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle repeat mode"><q-icon
          name="replay"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Download track" v-if="downloadURL"
        :href="downloadURL"><q-icon name="file_download"></q-icon></q-btn>
      <q-btn dense unelevated size="md" disable title="Download track" v-else><q-icon
          name="file_download"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" @click="singleLayoutMode = !singleLayoutMode"><q-icon
          name="screenshot_monitor" title="Toggle section details"
          :color="singleLayoutMode ? 'pink' : ''"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" @click="onShowTrackDetailsModal"><q-icon name="tag"
          title="Track tags details "></q-icon></q-btn>
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

const emit = defineEmits(['toggleAnalyzer']);

function onToggleAnalyzer() {
  emit('toggleAnalyzer');
}

function onToggleFavorite() {
  if (props.id) {
    const funct = ! props.favorited ? trackActions.setFavorite : trackActions.unSetFavorite;
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
