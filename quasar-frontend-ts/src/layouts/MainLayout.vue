<template>
  <q-layout view="lHh lpR lFf" class="theme-default-q-layout">
    <q-header height-hint="61.59" class="theme-default-q-header" bordered>
      <q-toolbar class="bg-grey-3 text-dark __theme-default-q-toolbar">
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
        <TopHeaderMenu :visible="!miniSidebarCurrentMode" />
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
    <q-footer elevated v-if="miniSidebarCurrentMode">
      <q-toolbar class="bg-grey-3 q-px-none">
        <div style="width: 8em">
          <!--
          <Vinyl :image="currentPlayListsStore.currentActivePlayListItem?.images?.small ?? null"
            :animated="playerStore.isPlaying" />
            -->
          <StaticAlbumCoverImage :image="currentPlayListsStore.currentActivePlayListItem?.images?.small ?? null" />
          <!--
          <TrackImage :src="currentPlayListsStore.currentActivePlayListItem?.images?.small ?? null"
            :rotate="playerStore.isPlaying" />
            -->
        </div>
        <div style="width: 30em" class="q-ml-sm text-dark">
          <p class="q-mb-none">{{ currentPlaylistItemStore.trackTitle }}</p>
          <p>by {{ currentPlaylistItemStore.trackArtistName }}</p>
          <p class="q-mb-none">{{ currentPlaylistItemStore.trackAlbumTitle }} ({{
            currentPlaylistItemStore.trackAlbumYear }})</p>
          <p>by {{ currentPlaylistItemStore.trackAlbumArtistName }}</p>
        </div>
        <MainControls />
        <div style="width: 25%;">
          <SeekControl />
        </div>
        <div style="width: 20%">
          <SidebarSpectrumAnalyzer />
        </div>
        <div style="width: 20em;">
          <VolumeControl />
        </div>
      </q-toolbar>
    </q-footer>
  </q-layout>
</template>

<script setup lang="ts">
import { ref, watch, computed, onMounted, onBeforeUnmount } from "vue";
import { useQuasar, LocalStorage } from "quasar";
import { useI18n } from "vue-i18n";

import { default as SidebarDrawer } from "src/components/SidebarDrawer.vue";
import { default as DarkModeButton } from "src/components/Buttons/DarkModeButton.vue";
import { default as SwitchLanguageButton } from "src/components/Buttons/SwitchLanguageButton.vue";
import { default as GitHubButton } from "src/components/Buttons/GitHubButton.vue";
import { GITHUB_PROJECT_URL } from "src/constants";

import { default as TopHeaderMenu } from "src/components/Menus/TopHeaderMenu.vue";
import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

import { default as MainControls } from "src/components/Widgets/Player/MainControls.vue";
import { default as SeekControl } from "src/components/Widgets/Player/SeekControl.vue";
import { default as SidebarSpectrumAnalyzer } from "src/components/Widgets/Visualizations/SidebarSpectrumAnalyzer.vue";
import { default as VolumeControl } from "src/components/Widgets/Player/VolumeControl.vue";
import { default as TrackImage } from "src/components/TrackImage.vue";
import { default as Vinyl } from "src/components/Widgets/Visualizations/Vinyl.vue";
import { default as StaticAlbumCoverImage } from "src/components/Widgets/Visualizations/StaticAlbumCoverImage.vue";
import { useCurrentPlaylistItemStore } from "src/stores/currentPlaylistItem";

import { useCurrentPlayListsStore } from "src/stores/currentPlayLists";

const $q = useQuasar();


const { t } = useI18n();


const currentPlaylistItemStore = useCurrentPlaylistItemStore();


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

const currentPlayListsStore = useCurrentPlayListsStore();
currentPlayListsStore.init().then(() => {
}).catch((error) => { console.error(error); }).finally(() => { });


onMounted(() => {
});

onBeforeUnmount(() => {
});

</script>