<template>
  <q-drawer v-bind="attrs" show-if-above bordered :width="430" :mini="mini" class=" fit theme-default-q-drawer">
    <!--
    <PlayerWidget></PlayerWidget>
    -->
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
      <q-item v-for="link in menuItems" :key="link.text" v-ripple clickable :to="{ name: link.routeName }"
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

interface MenuItem {
  icon: string;
  text: string;
  routeName: string;
  alternateRouteNames?: string;

};
const menuItems: MenuItem[] = [
  { icon: 'home', text: "Home", routeName: 'index' },
  {
    icon: 'queue_music',
    text: 'Current playlist',
    routeName: 'currentPlaylist'
  },
  {
    icon: 'person',
    text: 'Browse artists',
    routeName: 'artists'
  },
  /*
  {
    icon: 'search',
    text: 'Search',
    routeName: 'search'
  },
  */
  {
    icon: 'album',
    text: 'Browse albums',
    routeName: 'albums'
  },
  {
    icon: 'folder_open',
    text: 'Browse paths',
    routeName: 'paths'
  },

  {
    icon: 'library_music',
    text: 'Browse playlists',
    routeName: 'playlists'
  },
  {
    icon: 'radio',
    text: 'Browse radio stations',
    routeName: 'radioStations'
  },
  { icon: 'account_circle', text: "My profile", routeName: 'profile' },
];

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