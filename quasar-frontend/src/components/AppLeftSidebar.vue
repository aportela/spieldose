<template>
  <q-card class="q-mb-md" :class="{ 'fixed': $q.screen.gt.md }">
    <leftSidebarPlayer></leftSidebarPlayer>
  </q-card>
</template>

<script setup>
import { useQuasar } from "quasar";
import { default as leftSidebarPlayer } from 'components/SidebarPlayer.vue';

const $q = useQuasar();

</script>
