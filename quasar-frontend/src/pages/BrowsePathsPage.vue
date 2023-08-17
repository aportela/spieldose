<template>
  <leftSidebar></leftSidebar>
  <div>Paths</div>
</template>

<script>
import { defineComponent } from 'vue'
import { default as leftSidebar } from 'components/AppLeftSidebar.vue';

export default defineComponent({
  name: 'BrowsePathsPage',
  components: {
    'leftSidebar': leftSidebar
  }
})
</script>
