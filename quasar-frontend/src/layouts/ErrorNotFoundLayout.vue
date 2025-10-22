<template>
  <q-layout>
  </q-layout>
</template>

<script setup>

import { useI18n } from "vue-i18n";

import { default as DarkModeButton } from "src/components/Buttons/DarkModeButton.vue"
import { default as SwitchLanguageButton } from "src/components/Buttons/SwitchLanguageButton.vue"
import { default as GitHubButton } from "src/components/Buttons/GitHubButton.vue"
import { GITHUB_PROJECT_URL } from "src/constants"

const { t } = useI18n();

</script>

<style lang="css" scoped></style>