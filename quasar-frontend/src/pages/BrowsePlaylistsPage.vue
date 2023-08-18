<template>
  <q-page>
    <leftSidebar></leftSidebar>
    <div>Playlists</div>
  </q-page>
</template>

<script>
import { defineComponent } from 'vue'
import { default as leftSidebar } from 'components/AppLeftSidebar.vue';

export default defineComponent({
  name: 'BrowsePlaylistsPage',
  components: {
    'leftSidebar': leftSidebar
  }
})
</script>
