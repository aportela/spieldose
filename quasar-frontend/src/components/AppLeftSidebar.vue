<template>
  <q-card class="q-mb-md" :class="{ 'fixed' : $q.screen.gt.md }">
    <leftSidebarPlayer></leftSidebarPlayer>
    <!--
    <leftSidebarMenu></leftSidebarMenu>
    -->
  </q-card>
</template>

<script setup>
import { useQuasar } from "quasar";
import { default as leftSidebarPlayer } from 'components/SidebarPlayer.vue';
//import { default as leftSidebarMenu } from 'components/SidebarMenu.vue';

const $q = useQuasar();

</script>
