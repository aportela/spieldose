<template>
  <q-page>
    <leftSidebar></leftSidebar>
    <!-- content -->
    Artist: {{ artistName }}
  </q-page>
</template>

<script setup>

import { default as leftSidebar } from 'components/AppLeftSidebar.vue';

import { useRoute } from 'vue-router'
const route = useRoute()
const artistName = route.params.name;

</script>
