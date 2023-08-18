<template>
  <q-page>
    <leftSidebar></leftSidebar>
    <div>Search</div>
  </q-page>
</template>

<script>
import { defineComponent } from 'vue'
import { default as leftSidebar } from 'components/AppLeftSidebar.vue';

export default defineComponent({
  name: 'SearchPage',
  components: {
    'leftSidebar': leftSidebar
  }
})
</script>
