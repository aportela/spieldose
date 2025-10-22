<template>
  <q-btn v-bind="attrs" :icon="fabGithub" no-caps target="_blank">
    <slot></slot>
    <DesktopToolTip>{{ tooltip }}</DesktopToolTip>
  </q-btn>
</template>

<script setup>

import { useI18n } from "vue-i18n";

import { useAttrs, computed } from "vue";
import { fabGithub } from "@quasar/extras/fontawesome-v6";

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

const attrs = useAttrs();

const { t } = useI18n();

const tooltip = computed(() => t("Browse project page"));

</script>