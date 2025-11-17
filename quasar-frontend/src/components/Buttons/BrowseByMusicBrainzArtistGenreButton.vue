<template>
  <router-link :to="{ name: 'index', params: { genre: genre } }" class="text-decoration-none"
    aria-label="Browse by genre">
    <q-chip square clickable class="theme-default-q-chip shadow-1" :class="qChipClass">
      <q-avatar class="theme-default-q-avatar text-white bg-blue-6" v-if="icon">
        <q-icon :name="icon"></q-icon>
      </q-avatar>
      <q-avatar class="theme-default-q-avatar text-white bg-blue-6" v-if="caption">{{ caption }}</q-avatar>
      <div class="full-width text-center ellipsis">
        {{ genre }}
      </div>
      <DesktopToolTip>{{ t("Browse by musicbrainz genre: ", { genre: genre }) }}</DesktopToolTip>
    </q-chip>
  </router-link>
</template>

<script setup>

import { useI18n } from "vue-i18n";

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

const { t } = useI18n();

const props = defineProps({
  genre: {
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