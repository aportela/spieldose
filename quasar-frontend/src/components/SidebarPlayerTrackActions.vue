<template>
  <div id="current_track_actions">
    <ul class="list list--footer">
      <li class="text-h5"><q-icon name="reorder" class="cursor-pointer" title="Toggle navigation menu"></q-icon></li>
      <li class="text-h5"><q-icon name="bar_chart" class="cursor-pointer" title="Toggle analyzer"
          @click="onToggleAnalyzer"></q-icon></li>
      <li class="text-h5"><q-icon name="favorite" class="cursor-pointer" title="Love/unlove track"></q-icon></li>
      <li class="text-h5"><q-icon name="shuffle" class="cursor-pointer" title="Toggle random sort"></q-icon></li>
      <li class="text-h5"><q-icon name="replay" class="cursor-pointer" title="Toggle repeat mode"></q-icon></li>
      <li class="text-h5"><q-icon name="file_download" class="cursor-pointer" title="Download track"></q-icon></li>
      <li class="text-h5"><q-icon name="screenshot_monitor" class="cursor-pointer"
          title="Toggle section details"></q-icon></li>
    </ul>
  </div>
</template>
<style>
div#current_track_actions {
  padding: 1rem 4rem;
}

div#current_track_actions ul {
  display: flex;
  margin: 0;
  padding: 0;
  list-style-type: none;
  justify-content: space-between;
}
</style>
<script setup>

const emit = defineEmits(['toggleAnalyzer']);

function onToggleAnalyzer() {
  emit('toggleAnalyzer');
}
</script>
