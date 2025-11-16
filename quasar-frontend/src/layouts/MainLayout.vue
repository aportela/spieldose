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
        <!--
        <q-btn type="button" no-caps no-wrap align="left" outline :label="searchButtonLabel" icon="search"
          class="full-width no-caps theme-default-q-btn" @click.prevent="dialogs.fastSearch.visible = true">
          <DesktopToolTip anchor="bottom middle" self="top middle">{{ t("Click to open fast search")
          }}</DesktopToolTip>
        </q-btn>
        -->
        <!--
        <FastSearchSelector dense class="full-width"></FastSearchSelector>
        -->
        <q-btn-group flat class="q-ml-md">
          <q-btn stack v-for="item in menuItems" size="md" no-caps :icon="item.icon" :key="item.text"
            :to="item.routeName">{{ item.text
            }}</q-btn>
        </q-btn-group>
        <q-space></q-space>
        <q-btn-group flat class="q-ml-md">
          <DarkModeButton dense />
          <SwitchLanguageButton :short-labels="true" style="min-width: 9em" />
          <GitHubButton dense :href="GITHUB_PROJECT_URL" />
          <!--
          <NotificationsButton dense no-caps></NotificationsButton>
          -->
        </q-btn-group>
      </q-toolbar>
    </q-header>
    <SidebarDrawer v-model="visibleSidebar" :mini="miniSidebarCurrentMode"></SidebarDrawer>
    <q-page-container>
      <router-view class="q-pa-sm" />
    </q-page-container>
    <!-- main common dialogs block, this dialogs will be launched from ANY page so we declare here and manage with bus events -->
    <ReAuthDialog v-if="dialogs.reauth.visible" @success="onSuccessReauth" @close="dialogs.reauth.visible = false" />
    <!--
    <q-page-container class="bg-grey-3 q-mt-md">
      <q-page>
        <q-ajax-bar></q-ajax-bar>
        <div class="row">
          <div class="items-center q-px-md q-mb-none q-mx-auto" style="width: 431px; max-width: 431px;">
            <leftSidebar></leftSidebar>
          </div>
          <div class="q-pr-md col-xl col-lg col-md col-sm-12 col-xs-12 q-mb-none q-mx-auto"
            v-if="!session.getSingleLayoutMode">
            <router-view />
          </div>
        </div>
      </q-page>
    </q-page-container>
    <FullScreenVisualization v-if="showFullScreenVisualization">
    </FullScreenVisualization>
    -->
  </q-layout>
</template>

<script setup>

import { ref, reactive, watch, computed, onMounted, onBeforeUnmount } from "vue";
import { useQuasar, LocalStorage, uid } from "quasar";
import { useI18n } from "vue-i18n";

import { useLocalStorage } from "src/composables/useLocalStorage"
import { useSessionStore } from "src/stores/session";
import { useBus } from "src/composables/useBus";


import { default as SidebarDrawer } from "src/components/SidebarDrawer.vue"
//import { default as SearchDialog } from "src/components/Dialogs/SearchDialog.vue"
import { default as DarkModeButton } from "src/components/Buttons/DarkModeButton.vue"
import { default as SwitchLanguageButton } from "src/components/Buttons/SwitchLanguageButton.vue"
import { default as GitHubButton } from "src/components/Buttons/GitHubButton.vue"
import { GITHUB_PROJECT_URL } from "src/constants"
import { default as ReAuthDialog } from "src/components/Dialogs/ReAuthDialog.vue"

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";
//import { currentPlayListActions } from "src/boot/spieldose";

const $q = useQuasar();

const { t } = useI18n();

const session = useSessionStore();

const { bus } = useBus();

if (!session.isLoaded) {
  session.load();
}

const dialogs = reactive({
  reauth: {
    visible: false
  },
});

const reAuthEmitters = reactive([]);

const onSuccessReauth = () => {
  dialogs.reauth.visible = false;
  bus.emit("reAuthSucess", ({ to: reAuthEmitters }))
  reAuthEmitters.length = 0;
};

const lockminiSidebarCurrentModeMode = ref(false);

const visibleSidebar = ref($q.screen.gt.sm);


// toggle this for using current mini sidebar saved mode
const saveMiniSidebarMode = true;

const miniSidebarCurrentModeSavedMode = saveMiniSidebarMode ? LocalStorage.getItem("miniSidebarCurrentMode") : null;

if (saveMiniSidebarMode && miniSidebarCurrentModeSavedMode != null) {
  lockminiSidebarCurrentModeMode.value = true;
}

const miniSidebarCurrentMode = ref(miniSidebarCurrentModeSavedMode != null ? miniSidebarCurrentModeSavedMode == true : $q.screen.md);

const currentScreenSize = computed(() => $q.screen.name);

watch(currentScreenSize, (newValue) => {
  if (!lockminiSidebarCurrentModeMode.value) {
    miniSidebarCurrentMode.value = $q.screen.lt.lg;
  }
});

const searchButtonLabel = computed(() => $q.screen.gt.xs ? t('Search on Spieldose...') : '');

const onToggleminiSidebarCurrentMode = (value) => {
  miniSidebarCurrentMode.value = !miniSidebarCurrentMode.value;
  lockminiSidebarCurrentModeMode.value = true;
  if (saveMiniSidebarMode) {
    LocalStorage.set("miniSidebarCurrentMode", miniSidebarCurrentMode.value);
  }
}

onMounted(() => {
  bus.on("reAuthRequired", (msg) => {
    if (msg.emitter) {
      reAuthEmitters.push(msg.emitter);
    }
    dialogs.reauth.visible = true;
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthRequired");
});

/*
const spieldoseStore = useSpieldoseStore();
spieldoseStore.create();

const loading = ref(false);
const showFullScreenVisualization = ref(false);
const availableLocales = ref([
  {
    shortLabel: 'EN',
    label: 'English',
    value: 'en-US'
  },
  {
    shortLabel: 'ES',
    label: 'Español',
    value: 'es-ES'
  },
  {
    shortLabel: 'GL',
    label: 'Galego',
    value: 'gl-GL'
  }
]);
*/
//const defaultBrowserLocale = availableLocales.value.find((lang) => lang.value == defaultLocale);
//const selectedLocale = ref(defaultBrowserLocale || availableLocales.value[0]);
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

const links = [
  {
    name: 'dashboard',
    text: 'Dashboard',
    icon: 'analytics',
    linkRouteName: 'dashboard'
  },
  {
    name: 'current_playlist',
    text: 'Current playlist',
    icon: 'list_alt',
    linkRouteName: 'currentPlaylist'
  },
  /*
  {
    name: 'search',
    text: 'Search',
    icon: 'search',
    linkRouteName: 'search'
  },
  */
  {
    name: 'browse_artists',
    text: 'Browse artists',
    icon: 'person',
    linkRouteName: 'artists'
  },
  {
    name: 'browse_albums',
    text: 'Browse albums',
    icon: 'album',
    linkRouteName: 'albums'
  },
  {
    name: 'browse_paths',
    text: 'Browse paths',
    icon: 'folder_open',
    linkRouteName: 'paths'
  },
  {
    name: 'browse_playlists',
    text: 'Browse playlists',
    icon: 'list',
    linkRouteName: 'playlists'
  },
  {
    name: 'browse_radio_stations',
    text: 'Browse radio stations',
    icon: 'radio',
    linkRouteName: 'radioStations'
  },
  {
    name: 'profile',
    text: 'My profile',
    icon: 'person',
    linkRouteName: 'profile'
  }
];

/*
bus.on('showFullScreenVisualization', () => {
  showFullScreenVisualization.value = true;
});

bus.on('hideFullScreenVisualization', () => {
  showFullScreenVisualization.value = false;
});

function onSelectLocale(locale, save) {
  selectedLocale.value = locale;
  i18n.global.locale.value = locale.value;
  if (save) {
    session.saveLocale(locale.value);
  }
}

*/
function logout() {
  spieldoseStore.stop();
  api.auth
    .logout()
    .then((success) => {
      session.logout();
      router.push({
        name: "login",
      });
    })
    .catch((error) => {
      session.logout();
      // TODO: remove
      $q.notify({
        type: "negative",
        message: t("API Error: fatal error"),
        caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
      });
    });
}

/*
loading.value = true;
currentPlayListActions.restoreCurrentPlaylistElement().then((success) => {
  loading.value = false;
}).catch((error) => {
  loading.value = false;
  $q.notify({
    type: "negative",
    message: t("API Error: error restoring playlist"),
    caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
  });
});
*/
</script>
