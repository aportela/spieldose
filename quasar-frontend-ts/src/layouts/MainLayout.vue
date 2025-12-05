<template>
  <q-layout view="lHh lpR lFf" class="theme-default-q-layout">
    <q-header height-hint="61.59" class="theme-default-q-header" bordered>
      <q-toolbar class="theme-default-q-toolbar">
        <q-btn flat dense round @click="visibleSidebar = !visibleSidebar;" aria-label="Toggle drawer" icon="menu"
          v-show="!visibleSidebar" class="q-mr-md" />
        <q-btn flat dense round @click="onToggleminiSidebarCurrentMode" aria-label="Toggle drawer"
          :icon="miniSidebarCurrentMode ? 'arrow_forward_ios' : 'arrow_back_ios_new'" class="q-mr-md"
          v-show="visibleSidebar">
          <DesktopToolTip>{{ t(miniSidebarCurrentMode ? "Expand sidebar" : "Collapse sidebar") }}
          </DesktopToolTip>
        </q-btn>
        <q-btn type="button" no-caps no-wrap align="left" outline :label="searchButtonLabel" icon="search"
          class="full-width no-caps theme-default-q-btn" v-if="miniSidebarCurrentMode">
          <DesktopToolTip anchor="bottom middle" self="top middle">{{ t("Click to open fast search")
            }}</DesktopToolTip>
        </q-btn>
        <!--
        <FastSearchSelector dense class="full-width"></FastSearchSelector>
        -->
        <q-btn-group flat class="q-ml-md" v-if="!miniSidebarCurrentMode">
          <q-btn stack v-for="item in menuItems" size="md" no-caps :icon="item.icon" :key="item.text"
            :to="item.routeName">{{ item.text
            }}</q-btn>
        </q-btn-group>
        <q-space></q-space>
        <q-btn-group flat class="q-ml-md">
          <DarkModeButton dense />
          <SwitchLanguageButton :short-labels="true" style="min-width: 9em" />
          <GitHubButton dense :href="GITHUB_PROJECT_URL" />
        </q-btn-group>
      </q-toolbar>
    </q-header>
    <SidebarDrawer v-model="visibleSidebar" :mini="miniSidebarCurrentMode" />
    <q-page-container>
      <router-view class="q-pa-sm" />
    </q-page-container>
  </q-layout>
</template>

<script setup lang="ts">
import { ref, watch, computed, onMounted, onBeforeUnmount } from "vue";
import { useQuasar, LocalStorage } from "quasar";
import { useI18n } from "vue-i18n";
import { usePlayerStore } from "stores/player";
import { useCurrentPlaylistItemStore } from "src/stores/currentPlaylistItem";
import { randomTrack } from "src/composables/playlistActions";

import { default as SidebarDrawer } from "src/components/SidebarDrawer.vue";
import { default as DarkModeButton } from "src/components/Buttons/DarkModeButton.vue";
import { default as SwitchLanguageButton } from "src/components/Buttons/SwitchLanguageButton.vue";
import { default as GitHubButton } from "src/components/Buttons/GitHubButton.vue";
import { GITHUB_PROJECT_URL } from "src/constants";

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

const $q = useQuasar();

const { t } = useI18n();

const lockminiSidebarCurrentModeMode = ref<boolean>(false);

const visibleSidebar = ref($q.screen.gt.sm);

// toggle this for using current mini sidebar saved mode
const saveMiniSidebarMode = true;

const miniSidebarCurrentModeSavedMode = saveMiniSidebarMode ? LocalStorage.getItem("miniSidebarCurrentMode") : null;

if (saveMiniSidebarMode && miniSidebarCurrentModeSavedMode != null) {
  lockminiSidebarCurrentModeMode.value = true;
}

const miniSidebarCurrentMode = ref(miniSidebarCurrentModeSavedMode != null ? miniSidebarCurrentModeSavedMode == true : $q.screen.md);

const currentScreenSize = computed(() => $q.screen.name);

const menuItems = [
  { icon: 'home', text: "Index", routeName: 'index' },
  { icon: 'search', text: "Search", routeName: 'search' },

  {
    icon: 'analytics',
    text: 'Dashboard',
    routeName: 'dashboard'
  },

  {
    icon: 'list_alt',
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
    icon: 'list',
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

watch(currentScreenSize, () => {
  if (!lockminiSidebarCurrentModeMode.value) {
    miniSidebarCurrentMode.value = $q.screen.lt.lg;
  }
});

const searchButtonLabel = computed(() => $q.screen.gt.xs ? t('Search on Spieldose...') : '');

const onToggleminiSidebarCurrentMode = () => {
  miniSidebarCurrentMode.value = !miniSidebarCurrentMode.value;
  lockminiSidebarCurrentModeMode.value = true;
  if (saveMiniSidebarMode) {
    LocalStorage.set("miniSidebarCurrentMode", miniSidebarCurrentMode.value);
  }
}


const playerStore = usePlayerStore();
const currentPlaylistItemStore = useCurrentPlaylistItemStore();

watch(
  () => currentPlaylistItemStore.t,
  (newValue) => {
    if (currentPlaylistItemStore.isTrack) {
      playerStore.setAudioSource(
        "/api2/file/raw/" + currentPlaylistItemStore.trackFileId,
      );
      if (playerStore.hasPreviousUserInteractions) {
        playerStore.play(true);
      }
    }
  },
);


onMounted(() => {
  playerStore.create();
  randomTrack();
});

onBeforeUnmount(() => {
});

</script>