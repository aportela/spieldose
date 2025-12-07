<template>
  <q-btn v-bind="attrs" :label="shortLabels ? selectedLocale.shortLabel : selectedLocale.label" icon="language"
    :icon-right="availableLocaleSelectorOptionItems.length > 1 ? 'unfold_more' : undefined" no-caps dense
    :disable="availableLocaleSelectorOptionItems.length <= 1">
    <DesktopToolTip>{{ tooltip }}</DesktopToolTip>
    <q-menu fit anchor="top left" self="bottom left" v-if="availableLocaleSelectorOptionItems.length > 1">
      <q-item dense clickable v-close-popup v-for="availableLanguage in availableLocaleSelectorOptionItems"
        :key="availableLanguage.value" @click="onSetLocale(availableLanguage.value)">
        <q-item-section>{{ availableLanguage.label }}</q-item-section>
        <q-item-section avatar v-if="availableLanguage.value === selectedLocale.value">
          <q-icon name="check" />
        </q-item-section>
      </q-item>
    </q-menu>
  </q-btn>
</template>

<script setup lang="ts">
import { ref, computed, useAttrs, watch } from "vue";
import { useI18n } from "vue-i18n";
import { availableLocaleSelectorOptionItems, getlocaleSelectorOptionItem } from "src/i18n";
import { useI18nStore } from "src/stores/i18n";
import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

const { t } = useI18n();

const i18NStore = useI18nStore();

const attrs = useAttrs();

interface SwitchLanguageButtonProps {
  shortLabels?: boolean
};
withDefaults(defineProps<SwitchLanguageButtonProps>(), {
  shortLabels: false
});

const tooltip = computed(() => t("Switch language"));

const selectedLocale = ref(getlocaleSelectorOptionItem(i18NStore.currentLocale));

watch(() => i18NStore.currentLocale, () => {
  selectedLocale.value = getlocaleSelectorOptionItem(i18NStore.currentLocale);
});

const onSetLocale = (newLocale: string) => {
  i18NStore.setLocale(newLocale);
};
</script>
