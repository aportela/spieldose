<template>
  <q-select outlined dense v-model="genre" :options="filteredGenres" options-dense :label="t('Genre')" :disable="loading || disable"
    emit-value filled clearable=""
    :hint="!genre ? t('Minimum 3 characters to trigger autocomplete') : null" use-input
    input-debounce="0" @filter="onFilterGenres" @update:model-value="onChangeGenre">
  </q-select>
</template>

<script setup>
import { ref } from "vue";
import { api } from "boot/axios";
import { useQuasar } from "quasar";
import { useI18n } from "vue-i18n";

const props = defineProps(['disable', 'defaultGenre']);
const emit = defineEmits(['change']);

const { t } = useI18n();
const $q = useQuasar();
let availableGenres = [];
const genre = ref(props.defaultGenre || null);
const filteredGenres = ref([]);
const loading = ref(false);

function getAvailableGenres() {
  loading.value = true;
  api.artistGenres.get().then((success) => {
    availableGenres = success.data.genres;
    filteredGenres.value = availableGenres;
    loading.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: t("API Error: error loading artists genres"),
      caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
    });
    loading.value = false;
  });
}

function onFilterGenres(val, update, abort) {
  if (val.length < 3) {
    abort();
    return;
  }
  update(() => {
    const needle = val.toLowerCase();
    filteredGenres.value = availableGenres.filter(genre => genre.toLowerCase().indexOf(needle) > -1);
  });
}

function onChangeGenre(selectedGenre) {
  emit("change", selectedGenre);
}

getAvailableGenres();

</script>
