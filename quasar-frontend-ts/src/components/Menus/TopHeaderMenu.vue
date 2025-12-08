<template>
  <q-btn-group flat class="q-ml-md" v-if="visible">
    <q-btn stack size="md" no-caps icon="search" :label="t('Search')" />
    <q-btn stack v-for="item in sidebarMenuItems" size="md" no-caps :icon="item.icon" :key="item.text"
      :to="{ name: item.routeName }" :label="t(item.text)" />
    <q-btn stack size="md" no-caps icon="logout" @click="logout" :label="t('Sign out')" />
  </q-btn-group>
</template>

<script setup lang="ts">
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { api } from "src/composables/api";
import { useSessionStore } from "src/stores/session";
import { usePlayerStore } from "src/stores/player";
import { sidebarMenuItems } from "src/types/menu";

interface TopHeaderMenuProps {
  visible: boolean;
};

defineProps<TopHeaderMenuProps>();

const { t } = useI18n();
const router = useRouter();
const sessionStore = useSessionStore();
const playerStore = usePlayerStore();

const logout = () => {
  playerStore.stop();
  api.auth
    .logout()
    .then(() => {
      sessionStore.removeAccessToken();
      router.push({
        name: "login",
      }).catch((e) => {
        console.error(e);
      });
    })
    .catch((errorResponse) => {
      console.error(errorResponse);
      sessionStore.removeAccessToken();
      router.push({
        name: "login",
      }).catch((e) => {
        console.error(e);
      });
    });
};
</script>