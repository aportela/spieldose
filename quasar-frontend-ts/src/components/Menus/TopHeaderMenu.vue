<template>
  <q-btn-group flat class="q-ml-md" v-if="visible">
    <q-btn stack v-for="item in topHeaderMenuItems" size="md" no-caps :icon="item.icon" :key="item.text"
      :to="item.routeName">{{
        item.text
      }}</q-btn>
  </q-btn-group>
</template>

<script setup lang="ts">
import { type MenuItem, topHeaderMenuItems } from "src/types/menu-item";

interface TopHeaderMenuProps {
  visible: boolean;
};

defineProps<TopHeaderMenuProps>();

</script>