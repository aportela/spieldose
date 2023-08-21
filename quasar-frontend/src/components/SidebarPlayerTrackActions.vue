<template>
  <div id="current_track_actions">
    <q-btn-group spread>
      <q-btn dense unelevated size="md" :disabled="true"><q-icon name="reorder" class="cursor-pointer"
          title="Toggle navigation menu"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disabled="true"><q-icon name="bar_chart" class="cursor-pointer"
          title="Toggle analyzer" @click="onToggleAnalyzer"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disabled="true"><q-icon name="favorite" class="cursor-pointer"
          title="Love/unlove track"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disabled="true"><q-icon name="shuffle" class="cursor-pointer"
          title="Toggle random sort"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disabled="true"><q-icon name="replay" class="cursor-pointer"
          title="Toggle repeat mode"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disabled="true"><q-icon name="file_download" class="cursor-pointer"
          title="Download track"></q-icon></q-btn>
      <q-btn dense unelevated size="md" :disabled="true"><q-icon name="screenshot_monitor" class="cursor-pointer"
          title="Toggle section details"></q-icon></q-btn>
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
  disabled: Boolean
});

const emit = defineEmits(['toggleAnalyzer']);

function onToggleAnalyzer() {
  emit('toggleAnalyzer');
}

</script>
