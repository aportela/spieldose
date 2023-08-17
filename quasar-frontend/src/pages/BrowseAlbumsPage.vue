<template>
  <leftSidebar></leftSidebar>
  <div>Albums</div>
</template>

<script>
import { defineComponent } from 'vue'
import { default as leftSidebar } from 'components/AppLeftSidebar.vue';

export default defineComponent({
  name: 'BrowseAlbumsPage',
  components: {
    'leftSidebar': leftSidebar
  }
})
</script>
