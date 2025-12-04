<template>
  <q-btn v-bind="attrs" :label="shortLabels ? selectedLocale.shortLabel : selectedLocale.label" icon="language"
    :icon-right="availableLocales.length > 1 ? 'unfold_more' : undefined" no-caps dense
    :disable="availableLocales.length <= 1">
    <DesktopToolTip>{{ tooltip }}</DesktopToolTip>
    <q-menu fit anchor="top left" self="bottom left" v-if="availableLocales.length > 1">
      <q-item dense clickable v-close-popup v-for="availableLanguage in availableLocales" :key="availableLanguage.value"
        @click="onSelectLocale(availableLanguage.value)">
        <q-item-section>{{ availableLanguage.label }}</q-item-section>
        <q-item-section avatar v-if="availableLanguage.value === selectedLocale.value">
          <q-icon name="check" />
        </q-item-section>
      </q-item>
    </q-menu>
  </q-btn>
</template>

<script setup lang="ts">
import { computed, useAttrs, ref } from "vue";
import { useI18n } from "vue-i18n";

import { useI18nStore } from "src/stores/i18n";
import { availableSystemLocales, setQuasarLanguage } from "src/composables/i18n";

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

const { t } = useI18n();

const { locale: i18nInstanceCurrentLocale } = useI18n();

const i18NStore = useI18nStore();

const attrs = useAttrs();

interface SwitchLanguageButtonProps {
  shortLabels?: boolean
};
withDefaults(defineProps<SwitchLanguageButtonProps>(), {
  shortLabels: false
});

const tooltip = computed(() => t("Switch language"));

const localeMappings = [
  { shortLabel: "EN", label: "English", value: "en-US" },
  { shortLabel: "ES", label: "Español", value: "es-ES" },
  { shortLabel: "GL", label: "Galego", value: "gl-GL" },
];

const availableLocales = localeMappings.filter((l) => availableSystemLocales.includes(l.value));

const index = availableLocales.findIndex((l) => l.value === i18NStore.currentLocale);

const selectedLocale = ref(availableLocales[index >= 0 ? index : 0]!);

const onSelectLocale = (newLocale: string) => {
  setQuasarLanguage(newLocale);
  if (i18NStore.setLocale(newLocale)) {
    i18nInstanceCurrentLocale.value = i18NStore.currentLocale;
    selectedLocale.value = availableLocales.find(l => l.value === newLocale) ?? availableLocales[0]!;

  } else {
    console.error("Invalid locale", newLocale)
  }
};
</script>
