<template>
  <q-list>
    <q-item class="cursor-pointer non-selectable no-pointer-events rounded-borders q-ma-sm theme-default-q-item">
      <q-item-section avatar>
        <q-avatar square size="24px">
          <img src="icons/favicon-128x128.png" />
        </q-avatar>
      </q-item-section>
      <q-item-section>
        <q-item-label class="text-weight-bold text-uppercase">Spieldose</q-item-label>
      </q-item-section>
    </q-item>
    <q-item v-for="item in sidebarMenuItems" :key="item.text" v-ripple clickable :to="{ name: item.routeName }"
      class="rounded-borders q-ma-sm theme-default-q-item"
      :active="$route.name === item.routeName || (item.alternateRouteNames?.includes(String($route.name)))"
      active-class="theme-default-q-item-active">
      <DesktopToolTip anchor="center end" self="center left" :offset="[16, 0]">{{ t(item.text) }}</DesktopToolTip>
      <q-item-section avatar>
        <q-icon :name="item.icon" />
      </q-item-section>
      <q-item-section>
        <q-item-label>{{ t(item.text) }}</q-item-label>
      </q-item-section>
    </q-item>
    <q-item v-ripple clickable @click="logout" class="rounded-borders q-ma-sm theme-default-q-item">
      <DesktopToolTip anchor="center end" self="center left" :offset="[16, 0]">{{ t(" Sign out") }}</DesktopToolTip>
      <q-item-section avatar>
        <q-icon name="logout" />
      </q-item-section>
      <q-item-section>
        <q-item-label>{{ t("Sign out") }}</q-item-label>
      </q-item-section>
    </q-item>
  </q-list>
</template>

<script setup lang="ts">
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { api } from "src/composables/api";
import { useSessionStore } from "src/stores/session";
import { sidebarMenuItems } from "src/types/menu-item";
import { default as DesktopToolTip } from "../DesktopToolTip.vue";

const { t } = useI18n();
const router = useRouter();
const sessionStore = useSessionStore();

const logout = () => {
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