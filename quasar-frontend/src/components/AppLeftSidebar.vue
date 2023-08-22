<template>
  <q-card>
    <leftSidebarPlayer></leftSidebarPlayer>
    <leftSidebarMenu></leftSidebarMenu>
  </q-card>
</template>

<script setup>

import { default as leftSidebarPlayer } from 'components/SidebarPlayer.vue';
import { default as leftSidebarMenu } from 'components/SidebarMenu.vue';

</script>
