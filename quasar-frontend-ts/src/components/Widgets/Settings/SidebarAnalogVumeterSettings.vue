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
      <p class="q-mt-xl"><q-slider :disable="!visible" label label-always
          :label-value="'Smooth factor: ' + smoothFactor" v-model.number="smoothFactor" :min="0.1" :max="1.0"
          :step="0.1" /></p>
      <p><q-btn-toggle spread size="md" :disable="!visible" v-model="fps" toggle-color="primary" no-caps :options="[
        { label: '10fps', value: 10 },
        { label: '15fps', value: 15 },
        { label: '30fps', value: 30 },
        { label: '60fps', value: 60 },
        { label: '90fps', value: 90 },
        { label: '120fps', value: 120 },
        { label: '144fps', value: 144 },
        { label: 'unlimited fps', value: 0 },
      ]" /></p>
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

const smoothFactor = computed({
  get() {
    return store.currentSmoothFactor;
  },
  set(value) {
    store.setSmoothFactor(value);
  }
});

const fps = computed({
  get() {
    return (store.currentFPS);
  },
  set(value) {
    store.setFPS(value);
  }
});

</script>