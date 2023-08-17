<template>
  <leftSidebar></leftSidebar>
  <div>Artists</div>
</template>

<script>
import { defineComponent } from 'vue'
import { default as leftSidebar } from 'components/AppLeftSidebar.vue';

export default defineComponent({
  name: 'BrowseArtistsPage',
  components: {
    'leftSidebar': leftSidebar
  }
})
</script>
