<template>
  <p></p>
</template>

<script setup>
import { ref, onMounted, computed, watch, inject, nextTick } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { useQuasar, date } from "quasar";
import { api } from 'boot/axios';

const { t } = useI18n();
const $q = useQuasar();

const route = useRoute();
const router = useRouter();

const loading = ref(false);

function get(mbId, title, artist, year) {
  loading.value = true;
  api.album.get(mbId, title, artist, year).then((success) => {
  }).catch((error) => {
    loading.value = false;
    switch (error.response.status) {
      default:
        $q.notify({
          type: "negative",
          message: t("API Error: fatal error"),
          caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
        });
        break;
    }
  });
}

get(route.query.mbId ?? null, route.params.title, route.query.artistMbId || null, route.query.artistMbName || null, route.query.year || null);
</script>
