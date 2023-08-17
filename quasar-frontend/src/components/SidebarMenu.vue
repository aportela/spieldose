<template>
  <q-list bordered separator padding>
    <q-item v-for="link in links" :key="link.text" v-ripple clickable>
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
import { useRouter } from "vue-router";
import { useQuasar } from "quasar";
import { useSessionStore } from "stores/session";
import { api } from 'boot/axios'
import { useI18n } from 'vue-i18n'

const { t } = useI18n();
const $q = useQuasar();

const session = useSessionStore();

const router = useRouter();

const links = [
  {
    text: 'Dashboard',
    icon: 'analytics'
  },
  {
    text: 'Current playlist',
    icon: 'list_alt'
  },
  {
    text: 'Search',
    icon: 'search'
  },
  {
    text: 'Browse artists',
    icon: 'person'
  },
  {
    text: 'Browse albums',
    icon: 'album'
  },
  {
    text: 'Browse paths',
    icon: 'folder_open'
  },
  {
    text: 'Browse playlists',
    icon: 'list'
  },
  {
    text: 'Browse radio stations',
    icon: 'radio'
  }
];

function signOut() {
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
