<template>
  <q-select outlined dense v-model="tag" :options="filteredTags" options-dense :label="t('Tag')"
    :disable="loading || disable" emit-value filled clearable=""
    :hint="!tag ? t('Minimum 3 characters to trigger autocomplete') : null" use-input input-debounce="0"
    @filter="onFilterTags" @update:model-value="onChangeTag">
  </q-select>
</template>

<script setup>
import { ref } from "vue";
import { useAPI } from "src/composables/useAPI";
import { useQuasar } from "quasar";
import { useI18n } from "vue-i18n";

const props = defineProps(['disable', 'defaultTag']);
const emit = defineEmits(['change']);

const { t } = useI18n();
const $q = useQuasar();

const { api } = useAPI();

let availableTags = [];
const tag = ref(props.defaultTag || null);
const filteredTags = ref([]);
const loading = ref(false);

function getAvailableTags() {
  loading.value = true;
  api.cloud.getLastFMArtistTagCloud().then((success) => {
    availableTags = success.data.tags;
    filteredTags.value = availableTags;
    loading.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: t("API Error: error loading artists tags"),
      caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
    });
    loading.value = false;
  });
}

function onFilterTags(val, update, abort) {
  if (val.length < 3) {
    abort();
    return;
  }
  update(() => {
    const needle = val.toLowerCase();
    filteredTags.value = availableTags.filter(tag => tag.toLowerCase().indexOf(needle) > -1);
  });
}

function onChangeTag(selectedGenre) {
  emit("change", selectedGenre);
}

getAvailableTags();

</script>