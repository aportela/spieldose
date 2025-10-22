<template>
  <q-btn v-bind="attrs" :icon="currentDarkModeIcon" @click="toggleDarkMode">
    <DesktopToolTip>{{ tooltip }}</DesktopToolTip>
    <slot></slot>
  </q-btn>
</template>

<script setup>

import { useAttrs, computed } from "vue";
import { useI18n } from "vue-i18n";

import { useDarkMode } from 'src/composables/useDarkMode';
import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

const { t } = useI18n();
const attrs = useAttrs();

const { toggleDarkMode, isDarkModeActive } = useDarkMode();

const currentDarkModeIcon = computed(() => {
  return (isDarkModeActive ? "dark_mode" : "light_mode");
});

const toolTipLight = computed(() => t("Switch to light mode"));
const toolTipDark = computed(() => t("Switch to dark mode"));

const tooltip = computed(() => isDarkModeActive ? toolTipLight : toolTipDark);

</script>