<template>
  <q-page>
    <leftSidebar></leftSidebar>
    <div>Artists</div>
  </q-page>
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
