<template>
  <q-banner dense rounded class="theme-default-q-banner"
    :class="{ 'theme-default-q-banner-success': success, 'theme-default-q-banner-error': error, 'theme-default-q-banner-warning': warning }">
    <template v-slot:avatar v-if="success || error || warning">
      <q-icon size="sm" v-if="success" name="check" class="text-green" />
      <q-icon size="sm" v-else-if="error" name="error" class="text-red" />
      <q-icon size="sm" v-else-if="warning" name="warning" class="text-warning" />
    </template>
    <slot name="text">
      {{ t(text) }}
    </slot>
    <slot name="details"></slot>
  </q-banner>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";

const { t } = useI18n();

interface CustomBannerProps {
  text?: string,
  success?: boolean,
  error?: boolean,
  warning?: boolean
};

withDefaults(defineProps<CustomBannerProps>(), {
  text: "",
  success: false,
  error: false,
  warning: false,
});
</script>