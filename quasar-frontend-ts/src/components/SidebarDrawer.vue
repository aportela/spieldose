<template>
  <q-drawer v-bind="attrs" show-if-above bordered :width="430" :mini="mini" class=" fit theme-default-q-drawer">
    <SidebarPlayer v-if="!mini" />
    <SideBarMenu v-if="mini" />
  </q-drawer>
</template>

<script setup lang="ts">

import { computed, useAttrs } from "vue";
import { default as SidebarPlayer } from "./Widgets/SidebarPlayer.vue";
import { default as SideBarMenu } from "./Menus/SideBarMenu.vue";


const props = defineProps({
  mini: {
    type: Boolean,
    required: false,
    default: false
  }
});

const attrs = useAttrs();
const mini = computed(() => props.mini);

</script>