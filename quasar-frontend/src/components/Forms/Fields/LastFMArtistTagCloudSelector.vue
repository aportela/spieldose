<template>
  <CommonSelector :options="options" :label="t('Tag')" :disable="loading" :use-autocomplete="true" @change="onChange">
  </CommonSelector>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useAPI } from "src/composables/useAPI";
import { useQuasar } from "quasar";
import { useI18n } from "vue-i18n";

import { default as CommonSelector } from "./CommonSelector.vue";

const props = defineProps(['disable', 'defaultGenre']);
const emit = defineEmits(['change']);

const { t } = useI18n();
const $q = useQuasar();

const { api } = useAPI();

const options = reactive([]);
const loading = ref(false);

function onRefresh() {
  loading.value = true;
  api.cloud.getLastFMArtistTagCloud().then((success) => {
    try {
      options.length = 0;
      options.push(...success.data.items.map(
        (item) => { return ({ label: `${item.name} (${item.total})`, value: item.name }) }
      ));
    } catch (e) {
      console.error(e);
    }
    loading.value = false;
  }).catch((error) => {
    // TODO
    /*
    $q.notify({
      type: "negative",
      message: t("API Error: error loading artists genres"),
      caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
    });
    */
    loading.value = false;
  });
}

function onChange(selectedGenre) {
  emit("change", selectedGenre);
}

onMounted(() => {
  onRefresh();
});

</script>
