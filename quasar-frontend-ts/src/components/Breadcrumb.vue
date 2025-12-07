<template>
  <q-breadcrumbs>
    <q-breadcrumbs-el icon="home" label="Spieldose" />
    <slot name="current-breadcrumb" v-if="icon && label">
      <q-breadcrumbs-el :icon="icon" :label="t(label)" />
    </slot>
  </q-breadcrumbs>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";
const { t } = useI18n();

interface BreadCrumbProps {
  icon?: string;
  label?: string;
};

defineProps<BreadCrumbProps>();

</script>