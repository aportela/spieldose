<template>
  <q-layout view="hHh Lpr lff">

    <q-header>
      <q-toolbar class="bg-grey-3 text-black shadow-2 rounded-borders">
        <q-avatar square size="42px" class="q-mr-sm">
          <img src="icons/favicon-96x96.png" />
        </q-avatar>
        <q-toolbar-title>Spieldose</q-toolbar-title>
        <q-select ref="search" dense standout use-input hide-selected class="q-mx-md" filled color="pink" :stack-label="false"
          :label="t('Search...')" v-model="searchText" :options="filteredOptions" @filter="onFilter" style="width: 40%; max-width: 500px;">
          <template v-slot:no-option v-if="searching">
            <q-item>
              <q-item-section>
                <div class="text-center">
                  <q-spinner color="pink" size="32px" />
                </div>
              </q-item-section>
            </q-item>
          </template>
          <template v-slot:option="scope">
            <q-list class="bg-grey-2 text-dark">
              <q-item v-bind="scope.itemProps" @click="onPlayTrack(scope.opt.id)">
                <q-item-section side>
                  <q-icon name="music_note" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ scope.opt.label }}</q-item-label>
                  <q-item-label caption>{{ scope.opt.caption }}</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </template>
        </q-select>
        <q-space />
        <!--
        notice shrink property since we are placing it
        as child of QToolbar
        -->
        <q-tabs shrink>
          <q-route-tab v-for="link in links" :key="link.name" :to="{ name: link.linkRouteName }" :name="link.name"
            :icon="link.icon" :label="t(link.text)" no-caps inline-label exact />
        </q-tabs>
        <q-btn flat no-caps stack icon="language">
          {{ selectedLocale.shortLabel }}
          <q-icon name="arrow_drop_down" size="16px" />
          <q-menu auto-close>
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
          </q-menu>
        </q-btn>
        <q-btn icon="logout" :label="t('Signout')" flat no-caps stack @click="signOut" />
      </q-toolbar>
    </q-header>
    <!--
      <q-header elevated>
        <q-toolbar class="bg-pink text-white">
          <q-btn flat round dense icon="menu" class="q-mr-sm" />
          <q-avatar>
            <img src="https://cdn.quasar.dev/logo-v2/svg/logo-mono-white.svg">
          </q-avatar>
          <q-toolbar-title>Spieldose</q-toolbar-title>
          <q-btn flat round dense icon="whatshot" />
        </q-toolbar>
        <q-toolbar class="bg-grey-10 text-white">
          <q-img src="images/vinyl.png" width="90px" height="90px" />
          <q-toolbar-title>
            <div class="row">
              <div class="col">
                <h5>Title<br>Artist</h5>
              </div>
              <div class="col"></div>
              <div class="q-pa-md q-gutter-sm text-center">
                <q-btn round dense size="md" :disable="disabled || !allowSkipPrevious" @click="onSkipPrevious"><q-icon
                    name="skip_previous" title="Toggle navigation menu"></q-icon></q-btn>
                <q-btn round dense size="lg" :disable="disabled || !allowPlay" @click="onPlay" class="q-mx-md"><q-icon
                    :name="isPlaying ? 'pause' : 'play_arrow'" title="Toggle navigation menu"
                    :class="{ 'text-pink-6': isPlaying }"></q-icon></q-btn>
                <q-btn round dense size="md" :disable="disabled || !allowSkipNext" @click="onSkipNext"><q-icon
                    name="skip_next" title="Toggle navigation menu"></q-icon></q-btn>
              </div>
            </div>
          </q-toolbar-title>
        </q-toolbar>
      </q-header>
      -->
    <q-drawer side="left" persistent show-if-above :width="450" class="bg-grey-3">
      <leftSidebar></leftSidebar>
    </q-drawer>
    <!--
      <div class="row q-gutter-lg q-pa-lg">
        <div class="col" style="width: 400px; max-width: 400px;">
          <leftSidebar></leftSidebar>
        </div>
        <div class="col">
          <router-view />
        </div>
      </div>
      -->
    <q-page-container class="bg-grey-3 q-mt-lg">
      <q-ajax-bar></q-ajax-bar>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, computed, watch } from "vue";

import { useRouter } from "vue-router";
import { api } from 'boot/axios';
import { useSessionStore } from "stores/session";
import { useQuasar } from "quasar";
import { useI18n } from 'vue-i18n';
import { i18n, defaultLocale } from "src/boot/i18n";
import { default as leftSidebar } from 'components/AppLeftSidebar.vue';
import { usePlayer } from 'stores/player';
import { usePlayerStatusStore } from 'stores/playerStatus'
import { useCurrentPlaylistStore } from 'stores/currentPlaylist'


const { t } = useI18n();
const $q = useQuasar();
const router = useRouter();
const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();
const playerStatus = usePlayerStatusStore();
const audioElement = ref(null);

const session = useSessionStore();

const searchText = ref(null);

const filteredOptions = ref([]);

const searching = ref(false);

const searchResults = ref([]);

function onFilter(val, update) {
  if (val && val.trim().length > 0) {
    if (! searching.value) {
      filteredOptions.value = [];
      searching.value = true;
      update(() => {
        api.track.search({ text: val }, 1, 5, false, 'title', 'ASC')
          .then((success) => {
            searchResults.value = success.data.data.items;
            filteredOptions.value = searchResults.value.map((item) => {
              return ({ id: item.id, label: item.title, caption: t('fastSearchResultCaption', { artistName: item.artist.name, albumTitle: item.album.title, albumYear: item.album.year }) });
            });
            searching.value = false;
            return;
          })
          .catch((error) => {
            searching.value = false;
            $q.notify({
              type: "negative",
              message: t("API Error: fatal error"),
              caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
            });
            return;
          });
      });
    }
  } else {
    update(() => {
      filteredOptions.value = [];
    });
    return;
  }
}

function onPlayTrack(trackId) {
  const element = searchResults.value.find((element) => element.id == trackId);
  if (element) {
    currentPlaylist.saveElements([{ track: element }]);
    if (! playerStatus.isPlaying) {
      player.play();
    }
  }
}

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

function onSelectLocale(locale, save) {
  selectedLocale.value = locale;
  i18n.global.locale.value = locale.value;
  if (save) {
    session.saveLocale(locale.value);
  }
}

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
  {
    name: 'search',
    text: 'Search',
    icon: 'search',
    linkRouteName: 'search'
  },
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
  }
];

const currentElementURL = computed(() => {
  const currentTrack = currentPlaylist.getCurrentElement;
  if (currentTrack && currentTrack.track) {
    return (currentTrack.track.url);
  } else {
    //TODO
    return (null);
  }
});

watch(currentElementURL, (newValue) => {
  if (audioElement.value) {
    audioElement.value.src = newValue;
    if (player.hasPreviousUserInteractions) {
      player.play(true);
    }
  }
});

player.create();

audioElement.value = player.getElement;

currentPlaylist.load();

if (player.hasPreviousUserInteractions) {
  if (!playerStatus.isPlaying) {
    player.play(true);
  }
}

function signOut() {
  player.stop();
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

</script>
