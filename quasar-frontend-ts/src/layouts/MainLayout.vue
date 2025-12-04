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
          class="full-width no-caps theme-default-q-btn">
          <DesktopToolTip anchor="bottom middle" self="top middle">{{ t("Click to open fast search")
          }}</DesktopToolTip>
        </q-btn>
        <!--
        <FastSearchSelector dense class="full-width"></FastSearchSelector>
        -->
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


onMounted(() => {
});

onBeforeUnmount(() => {
});

</script>