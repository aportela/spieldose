<template>
  <div id="current_track_actions">
    <q-btn-group spread>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle navigation menu"><q-icon
          name="reorder"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle analyzer"><q-icon name="bar_chart"
          @click="onToggleAnalyzer"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Love/unlove track"><q-icon
          name="favorite" :color="favorited ? 'pink': ''" @click="onToggleFavorite"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle random sort"><q-icon
          name="shuffle"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Toggle repeat mode"><q-icon
          name="replay"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled" title="Download track" v-if="downloadURL"
        :href="downloadURL"><q-icon name="file_download"></q-icon></q-btn>
      <q-btn dense unelevated size="md" disable title="Download track" v-else><q-icon
          name="file_download"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disable="disabled"><q-icon name="screenshot_monitor"
          title="Toggle section details"></q-icon></q-btn>
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

const props = defineProps({
  disabled: Boolean,
  favorited: Number,
  downloadURL: String
});

const emit = defineEmits(['toggleAnalyzer', 'toggleFavorite']);

function onToggleAnalyzer() {
  emit('toggleAnalyzer');
}

function onToggleFavorite() {
  emit('toggleFavorite');
}

function onShowTrackDetailsModal() {
  emit('toggleTrackDetailsModal');
}

</script>
