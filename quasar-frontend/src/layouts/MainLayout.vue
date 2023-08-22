<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container>
      <div class="row q-gutter-lg q-pa-lg">
        <div class="col" style="width: 400px; max-width: 400px;">
          <leftSidebar></leftSidebar>
        </div>
        <div class="col">
          <router-view />
        </div>
      </div>
    </q-page-container>
  </q-layout>
</template>

<script setup>

import { default as leftSidebar } from 'components/AppLeftSidebar.vue';

</script>
