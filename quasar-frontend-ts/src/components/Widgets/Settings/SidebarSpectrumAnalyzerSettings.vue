<template>
  <q-card class="full-width">
    <q-item class="theme-default-q-card-section-header">
      {{ t('Sidebar spectrum analyzer settings') }}
    </q-item>
    <q-separator />
    <q-card-section>
      <p>
        <q-toggle v-model="visible" label="visible" />
        <q-toggle v-model="loRes" :disable="!visible" label="Low resolution" />
      </p>
      <p>
        <q-toggle v-model="showPeaks" :disable="!visible" label="show peaks" />
        <q-toggle v-model="ledBars" :disable="!visible" label="led bars" />
        <q-toggle v-model="trueLeds" :disable="!visible || !ledBars" label="true leds" />
      </p>
      <p class="q-mt-lg"><q-slider :disable="!visible" label label-always :label-value="'Height: ' + height + 'px'"
          v-model="height" :min="30" :max="180" :step="1" /></p>
      <p><q-btn-toggle spread size="md" :disable="!visible" v-model="colorMode" toggle-color="primary" no-caps :options="[
        { label: 'gradient', value: 'gradient' },
        { label: 'bar-index', value: 'bar-index' },
        { label: 'bar-level', value: 'bar-level' },
      ]" /></p>
      <p><q-btn-toggle spread size="md" :disable="!visible" v-model="gradient" toggle-color="primary" no-caps :options="[
        { label: 'spieldose', value: 'spieldose' },
        { label: 'classic', value: 'classic' },
        { label: 'orangered', value: 'orangered' },
        { label: 'prism', value: 'prism' },
        { label: 'rainbow', value: 'rainbow' },
        { label: 'steelblue', value: 'steelblue' },
      ]" /></p>
      <p><q-btn-toggle spread size="md" :disable="!visible" v-model="channelLayout" toggle-color="primary" no-caps
          :options="[
            { label: 'Single channel', value: 'single' },
            { label: 'Dual channel (overlay)', value: 'dual-combined' },
            { label: 'Dual channel (side by side)', value: 'dual-horizontal' },
            { label: 'Dual channel (top/bottom)', value: 'dual-vertical' },
          ]" /></p>
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
      <p><q-btn-toggle spread size="md" :disable="!visible" v-model="mode" toggle-color="primary" no-caps :options="[
        { label: 'all', value: 0 },
        { label: '240 bands', value: 1 },
        { label: '120 bands', value: 2 },
        { label: '80 bands', value: 3 },
        { label: '60 bands', value: 4 },
        { label: '40 bands', value: 5 },
        { label: '30 bands', value: 6 },
        { label: '20 bands', value: 7 },
        { label: '10 bands', value: 8 },
        { label: 'line/area graph', value: 10 },
      ]" /></p>
      <p class="q-mt-xl"><q-slider :disable="!visible" label label-always :label-value="'Bar space: ' + barSpace"
          v-model.number="barSpace" :min="0.0" :max="1.0" :step="0.01" /></p>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">

import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { useSidebarSpectrumAnalyzerSettingsStore } from "src/stores/sidebarSpectrumAnalyzerSettings";

const { t } = useI18n();

const store = useSidebarSpectrumAnalyzerSettingsStore();

const visible = computed({
  get() {
    return store.visible;
  },
  set(value) {
    store.setVisibility(value);
  }
});

const showPeaks = computed({
  get() {
    return (store.showPeaks);
  },
  set(value) {
    store.setPeaksVisibility(value);
  }
});

const ledBars = computed({
  get() {
    return (store.ledBars);
  },
  set(value) {
    store.setLedBars(value);
  }
});

const trueLeds = computed({
  get() {
    return (store.trueLeds);
  },
  set(value) {
    store.setTrueLeds(value);
  }
});

const loRes = computed({
  get() {
    return (store.loRes);
  },
  set(value) {
    store.setLoRes(value);
  }
});

const channelLayout = computed({
  get() {
    return (store.channelLayout);
  },
  set(value) {
    store.setChannelLayout(value);
  }
});

const fps = computed({
  get() {
    return (store.fps);
  },
  set(value) {
    store.setFPS(value);
  }
});

const mode = computed({
  get() {
    return (store.mode);
  },
  set(value) {
    store.setMode(value);
  }
});

const barSpace = computed({
  get() {
    return (store.barSpace);
  },
  set(value) {
    store.setBarSpace(value);
  }
});

const height = computed({
  get() {
    return (store.height);
  },
  set(value) {
    store.setHeight(value);
  }
});

const colorMode = computed({
  get() {
    return (store.colorMode);
  },
  set(value) {
    store.setColorMode(value);
  }
});

const gradient = computed({
  get() {
    return (store.gradient);
  },
  set(value) {
    store.setGradient(value);
  }
});

</script>