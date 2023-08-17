<template>
  <leftSidebar></leftSidebar>
  <div>Current playlist</div>
</template>

<script>
import { defineComponent } from 'vue'
import { default as leftSidebar } from 'components/AppLeftSidebar.vue';

export default defineComponent({
  name: 'CurrentPlaylistPage',
  components: {
    'leftSidebar': leftSidebar
  }
})
</script>
