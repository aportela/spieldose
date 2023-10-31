<template>
  <q-layout view="hHh Lpr lff">
    <q-header>
      <q-toolbar class="bg-grey-3 text-black shadow-1">
        <q-avatar square size="42px" class="q-mr-sm">
          <img src="icons/favicon-96x96.png" />
        </q-avatar>
        <q-toolbar-title>Spieldose</q-toolbar-title>
        <q-space />
        <ToolbarSearch :disable="loading"></ToolbarSearch>
        <!--
        notice shrink property since we are placing it
        as child of QToolbar
        -->
        <q-tabs shrink dense no-caps>
          <q-route-tab v-for="link in links" :key="link.name" :to="{ name: link.linkRouteName }" :name="link.name"
            :icon="link.icon" :label="$q.screen.gt.md ? t(link.text) : ''" :title="t(link.text)" no-caps inline-label
            :disable="loading" />
          <q-btn-dropdown icon="language" auto-close stretch flat :label="selectedLocale.shortLabel" stack
            :disable="loading">
            <q-list dense style="min-width: 200px">
              <q-item class="GL__menu-link-signed-in">
                <q-item-section>
                  <div>{{ t("Selected language") }}: <strong>{{ selectedLocale.label }}</strong></div>
                </q-item-section>
              </q-item>
              <q-separator />
              <q-item clickable :disable="selectedLocale.value == availableLocale.value" v-close-popup
                v-for="availableLocale in availableLocales" :key="availableLocale.value"
                @click="onSelectLocale(availableLocale, true)">
                <q-item-section>
                  <div>{{ availableLocale.label }}</div>
                </q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
          <q-btn round dense flat stretch :icon="fabGithub" color="dark" no-caps
            href="http://github.com/aportela/spieldose" target="_blank" :disable="loading" />
          <q-btn stretch icon="logout" :label="$q.screen.xl ? t('Signout') : ''" :title="t('Signout')" flat no-caps stack
            @click="signOut" :disable="loading" />
        </q-tabs>

      </q-toolbar>
    </q-header>
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
  </q-layout>
</template>

<script setup>
import { ref, computed, watch } from "vue";

import { useRouter } from "vue-router";
import { api } from "boot/axios";
import { useSessionStore } from "stores/session";
import { useQuasar } from "quasar";
import { useI18n } from "vue-i18n";
import { i18n, defaultLocale } from "src/boot/i18n";
import { default as leftSidebar } from "components/AppLeftSidebar.vue";
import { default as ToolbarSearch } from "components/ToolbarSearch.vue";
import { default as FullScreenVisualization } from "components/FullScreenVisualization.vue";
import { bus } from "boot/bus";
import { fabGithub } from "@quasar/extras/fontawesome-v6";
import { useSpieldoseStore } from "stores/spieldose";
import { currentPlayListActions } from "src/boot/spieldose";

const { t } = useI18n();
const $q = useQuasar();
const router = useRouter();

const session = useSessionStore();
if (!session.isLoaded) {
  session.load();
}

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
const defaultBrowserLocale = availableLocales.value.find((lang) => lang.value == defaultLocale);
const selectedLocale = ref(defaultBrowserLocale || availableLocales.value[0]);
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

function signOut() {
  spieldoseStore.stop();
  api.user
    .signOut()
    .then((success) => {
      session.signOut();
      router.push({
        name: "signIn",
      });
    })
    .catch((error) => {
      $q.notify({
        type: "negative",
        message: t("API Error: fatal error"),
        caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
      });
    });
}

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
</script>
