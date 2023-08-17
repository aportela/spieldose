<template>
  <leftSidebar></leftSidebar>
</template>

<script>
import { defineComponent } from 'vue'
import { default as leftSidebar } from 'components/AppLeftSidebar.vue';

export default defineComponent({
  name: 'DashboardPage',
  components: {
    'leftSidebar': leftSidebar
  }
})
</script>
