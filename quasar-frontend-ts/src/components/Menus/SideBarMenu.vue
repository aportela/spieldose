<template>
  <q-item v-for="link in sidebarMenuItems" :key="link.text" v-ripple clickable :to="{ name: link.routeName }"
    class="rounded-borders q-ma-sm theme-default-q-item"
    :active="$route.name === link.routeName || (link.alternateRouteNames?.includes(String($route.name)))"
    active-class="theme-default-q-item-active">
    <q-item-section avatar>
      <q-icon :name="link.icon" />
    </q-item-section>
    <q-item-section>
      <q-item-label>{{ t(link.text) }}</q-item-label>
    </q-item-section>
  </q-item>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";
import { sidebarMenuItems } from "src/types/menu-item";

const { t } = useI18n();


</script>