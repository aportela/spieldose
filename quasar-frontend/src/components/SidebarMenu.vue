<template>
  <q-list bordered separator padding>
    <q-item v-for="link in links" :key="link.text" v-ripple clickable :to="{ name: link.linkRouteName }">
      <q-item-section avatar>
        <q-icon color="grey" :name="link.icon" />
      </q-item-section>
      <q-item-section>
        <q-item-label>{{ link.text }}</q-item-label>
      </q-item-section>
    </q-item>
    <q-item v-ripple clickable @click="signOut">
      <q-item-section avatar>
        <q-icon color="grey" name="logout" />
      </q-item-section>
      <q-item-section>
        <q-item-label>Signout</q-item-label>
      </q-item-section>
    </q-item>
  </q-list>
</template>

<script setup>
import { inject } from "vue";

import { useRouter } from "vue-router";
import { useQuasar } from "quasar";
import { useSessionStore } from "stores/session";
import { api } from 'boot/axios';
import { useI18n } from 'vue-i18n';
import { useSpieldoseStore } from "stores/spieldose";

const spieldoseStore = useSpieldoseStore();

const { t } = useI18n();
const $q = useQuasar();

const session = useSessionStore();
if (!session.isLoaded) {
  session.load();
}

const router = useRouter();

const links = [
  {
    text: 'Dashboard',
    icon: 'analytics',
    linkRouteName: 'dashboard'
  },
  {
    text: 'Current playlist',
    icon: 'list_alt',
    linkRouteName: 'currentPlaylist'
  },
  {
    text: 'Search',
    icon: 'search',
    linkRouteName: 'search'
  },
  {
    text: 'Browse artists',
    icon: 'person',
    linkRouteName: 'artists'
  },
  {
    text: 'Browse albums',
    icon: 'album',
    linkRouteName: 'albums'
  },
  {
    text: 'Browse paths',
    icon: 'folder_open',
    linkRouteName: 'paths'
  },
  {
    text: 'Browse playlists',
    icon: 'list',
    linkRouteName: 'playlists'
  },
  {
    text: 'Browse radio stations',
    icon: 'radio',
    linkRouteName: 'radioStations'
  }
];

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
        caption: t("API Error: fatal error details", { status: error && error.response ? error.response.status : 'undefined', statusText: error && error.response ? error.response.statusText : 'undefined' })
      });
    });
}

</script>
