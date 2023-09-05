<template>
  <q-layout view="hHh Lpr lff">

    <q-header>
      <q-toolbar class="bg-grey-3 text-black shadow-2 rounded-borders">
        <q-toolbar-title>Spieldose</q-toolbar-title>
        <q-input></q-input>
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
          {{ selectedLanguage.shortLabel }}
          <q-icon name="arrow_drop_down" size="16px" />
          <q-menu auto-close>
            <q-list dense style="min-width: 200px">
              <q-item class="GL__menu-link-signed-in">
                <q-item-section>
                  <div>{{ t("Selected language") }}: <strong>{{ selectedLanguage.label }}</strong></div>
                </q-item-section>
              </q-item>
              <q-separator />
              <q-item clickable :disable="selectedLanguage.value == availableLanguage.value" v-close-popup
                v-for="availableLanguage in availableLanguages" :key="availableLanguage.value"
                @click="onSelectLanguage(availableLanguage, true)">
                <q-item-section>
                  <div>{{ availableLanguage.label }}</div>
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
import { ref, computed, onMounted, watch } from "vue";

import { useRouter } from "vue-router";
import { api } from 'boot/axios';
import { useSessionStore } from "stores/session";
import { useQuasar } from "quasar";
import { useI18n } from 'vue-i18n';
import { i18n } from "src/boot/i18n";
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

const availableLanguages = ref([
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

const previousLang = availableLanguages.value.find((lang) => lang.value == session.lang);

const selectedLanguage = ref(previousLang || availableLanguages.value[0]);
onSelectLanguage(selectedLanguage.value, false);

function onSelectLanguage(language, save) {
  selectedLanguage.value = language;
  i18n.global.locale.value = language.value;
  if (save) {
    session.saveLang(language.value);
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

const currentTrackURL = computed(() => {
  const currentTrack = currentPlaylist.getCurrentTrack;
  if (currentTrack) {
    if (!currentTrack.radioStation) {
      return (currentTrack.url);
    } else {
      return (currentTrack.radioStation.url);
    }
  } else {
    return (null);
  }

});

watch(currentTrackURL, (newValue) => {
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
