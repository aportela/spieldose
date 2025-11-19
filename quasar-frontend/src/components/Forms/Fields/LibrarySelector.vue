<template>
  <CommonSelector :options="options" :label="t('Library')" :disable="loading" :use-autocomplete="false"
    @change="onChange" :default-value="defaultValue">
  </CommonSelector>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useAPI } from "src/composables/useAPI";
import { useQuasar } from "quasar";
import { useI18n } from "vue-i18n";

import { default as CommonSelector } from "./CommonSelector.vue";

const props = defineProps(['disable',]);
const emit = defineEmits(['change']);

const { t } = useI18n();
const $q = useQuasar();

const { api } = useAPI();

const options = reactive([]);
const loading = ref(false);
const defaultValue = ref(null);

function onRefresh() {
  loading.value = true;
  api.browse.libraries().then((success) => {
    try {
      options.length = 0;
      options.push(...success.data.data.items.map(
        (item) => { return ({ label: item.name, value: item.id }) }
      ));
      defaultValue.value = options[0].value;

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

function onChange(selectedValue) {
  emit("change", selectedValue);
}

onMounted(() => {
  onRefresh();
});

</script>