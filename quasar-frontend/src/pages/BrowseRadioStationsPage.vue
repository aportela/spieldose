<template>
  <leftSidebar></leftSidebar>
  <div>Radio stations</div>
</template>

<script>
import { defineComponent } from 'vue'
import { default as leftSidebar } from 'components/AppLeftSidebar.vue';

export default defineComponent({
  name: 'BrowseRadioStationsPage',
  components: {
    'leftSidebar': leftSidebar
  }
})
</script>
