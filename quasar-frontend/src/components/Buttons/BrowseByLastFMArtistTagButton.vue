<template>
  <router-link :to="{ name: 'index', params: { tag: tag } }" class="text-decoration-none" aria-label="Browse by tag">
    <q-chip square clickable class="theme-default-q-chip shadow-1" :class="qChipClass">
      <q-avatar class="theme-default-q-avatar text-white bg-blue-6" v-if="icon">
        <q-icon :name="icon"></q-icon>
      </q-avatar>
      <q-avatar class="theme-default-q-avatar text-white bg-blue-6" v-if="caption">{{ caption }}</q-avatar>
      <div class="full-width text-center ellipsis">
        {{ tag }}
      </div>
      <DesktopToolTip>{{ t("Browse by LastFM tag: ", { tag: tag }) }}</DesktopToolTip>
    </q-chip>
  </router-link>
</template>

<script setup>

import { useI18n } from "vue-i18n";

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

const { t } = useI18n();

const props = defineProps({
  tag: {
    type: String,
    required: true,
  },
  icon: {
    type: String,
    required: false
  },
  caption: {
    type: String,
    required: false
  },
  toolTip: {
    type: Boolean,
    required: false,
    default: true
  },
  qChipClass: {
    type: String,
    required: false
  }
});

</script>