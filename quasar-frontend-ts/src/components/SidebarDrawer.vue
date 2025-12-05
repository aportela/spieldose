<template>
  <q-drawer v-bind="attrs" show-if-above bordered :width="430" :mini="mini" class=" fit theme-default-q-drawer">
    <SidebarPlayer v-if="!mini" />
    <q-list v-if="mini">
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
      <SideBarMenu />
      <q-item v-ripple clickable @click="logout" class="rounded-borders q-ma-sm theme-default-q-item">
        <q-item-section avatar>
          <q-icon name="logout" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ t("Sign out") }}</q-item-label>
        </q-item-section>
      </q-item>
    </q-list>
  </q-drawer>
</template>

<script setup lang="ts">

import { computed, useAttrs } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { api } from "src/composables/api";
import { useSessionStore } from "src/stores/session";
import { default as SidebarPlayer } from "./Widgets/SidebarPlayer.vue";
import SideBarMenu from "./Menus/SideBarMenu.vue";

//import { default as PlayerWidget } from "./Player/PlayerWidget.vue";

const props = defineProps({
  mini: {
    type: Boolean,
    required: false,
    default: false
  }
});

const attrs = useAttrs();

const { t } = useI18n();
const router = useRouter();


const sessionStore = useSessionStore();

const mini = computed(() => props.mini);

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