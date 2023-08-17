<template>
  <div>sidebar</div>
  <q-drawer v-model="leftDrawerOpen" show-if-above bordered :width="400">
    <leftSidebarPlayer></leftSidebarPlayer>
    <leftSidebarMenu></leftSidebarMenu>
  </q-drawer>
</template>

<script setup>

import { default as leftSidebarPlayer } from 'components/SidebarPlayer.vue';
import { default as leftSidebarMenu } from 'components/SidebarMenu.vue';

const leftDrawerOpen = true;
</script>
