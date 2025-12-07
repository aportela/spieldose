<template>
  <router-view />
</template>

<script setup lang="ts">
import { watch } from "vue";
import { useI18n } from "vue-i18n";
import { useI18nStore } from "src/stores/i18n";
import { setQuasarLanguage } from "./i18n";

const { locale: i18nInstanceCurrentLocale } = useI18n();

const i18nStore = useI18nStore();
setQuasarLanguage(i18nStore.currentLocale); // init current locale on Quasar native controls

// watch for locale changes in store
watch(() => i18nStore.currentLocale, (newValue) => {
  i18nInstanceCurrentLocale.value = newValue; // update current locale for i18n global instance (custom text translations)
  setQuasarLanguage(newValue); // update current locale on Quasar native controls
});

</script>
