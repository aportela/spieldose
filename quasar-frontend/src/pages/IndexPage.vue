<template>
  <q-page class="flex flex-center">
    <p>
      <q-btn @click="signOut">signout</q-btn>
    </p>
  </q-page>
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
