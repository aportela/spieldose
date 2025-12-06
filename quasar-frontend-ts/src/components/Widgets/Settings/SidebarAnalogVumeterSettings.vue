<template>
  <q-card class="full-width">
    <q-item class="theme-default-q-card-section-header">
      {{ t('Sidebar analog vumeter settings') }}
    </q-item>
    <q-separator />
    <q-card-section>
      <p>
        <q-toggle v-model="visible" label="visible" />
      </p>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">

import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { useSidebarAnalogVumeterSettingsStore } from "src/stores/sidebarAnalogVumeterSettings";

const { t } = useI18n();

const store = useSidebarAnalogVumeterSettingsStore();

const visible = computed({
  get() {
    return store.visible;
  },
  set(value) {
    store.setVisibility(value);
  }
});

</script>