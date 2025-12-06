<template>
  <q-card class="full-width">
    <q-item class="theme-default-q-card-section-header">
      {{ t('Sidebar mini spectrum analyzer settings') }}
    </q-item>
    <q-separator />
    <q-card-section>
      <p>
        <q-toggle v-model="showMiniSpectrumAnalyzer" label="visible" />
        <q-toggle v-model="loRes" :disable="!showMiniSpectrumAnalyzer" label="Low resolution" />
      </p>
      <p>
        <q-toggle v-model="showPeaks" :disable="!showMiniSpectrumAnalyzer" label="show peaks" />
        <q-toggle v-model="ledBars" :disable="!showMiniSpectrumAnalyzer" label="led bars" />
        <q-toggle v-model="trueLeds" :disable="!showMiniSpectrumAnalyzer || !ledBars" label="true leds" />
      </p>
      <p class="q-mt-lg"><q-slider :disable="!showMiniSpectrumAnalyzer" label label-always
          :label-value="'Height: ' + height + 'px'" v-model="height" :min="30" :max="180" :step="1" /></p>
      <p><q-btn-toggle spread size="md" :disable="!showMiniSpectrumAnalyzer" v-model="colorMode" toggle-color="primary"
          no-caps :options="[
            { label: 'gradient', value: 'gradient' },
            { label: 'bar-index', value: 'bar-index' },
            { label: 'bar-level', value: 'bar-level' },
          ]" /></p>
      <p><q-btn-toggle spread size="md" :disable="!showMiniSpectrumAnalyzer" v-model="gradient" toggle-color="primary"
          no-caps :options="[
            { label: 'spieldose', value: 'spieldose' },
            { label: 'classic', value: 'classic' },
            { label: 'orangered', value: 'orangered' },
            { label: 'prism', value: 'prism' },
            { label: 'rainbow', value: 'rainbow' },
            { label: 'steelblue', value: 'steelblue' },
          ]" /></p>
      <p><q-btn-toggle spread size="md" :disable="!showMiniSpectrumAnalyzer" v-model="channelLayout"
          toggle-color="primary" no-caps :options="[
            { label: 'Single channel', value: 'single' },
            { label: 'Dual channel (overlay)', value: 'dual-combined' },
            { label: 'Dual channel (side by side)', value: 'dual-horizontal' },
            { label: 'Dual channel (top/bottom)', value: 'dual-vertical' },
          ]" /></p>
      <p><q-btn-toggle spread size="md" :disable="!showMiniSpectrumAnalyzer" v-model="fps" toggle-color="primary"
          no-caps :options="[
            { label: '10fps', value: 10 },
            { label: '15fps', value: 15 },
            { label: '30fps', value: 30 },
            { label: '60fps', value: 60 },
            { label: '90fps', value: 90 },
            { label: '120fps', value: 120 },
            { label: '144fps', value: 144 },
            { label: 'unlimited fps', value: 0 },
          ]" /></p>
      <p><q-btn-toggle spread size="md" :disable="!showMiniSpectrumAnalyzer" v-model="mode" toggle-color="primary"
          no-caps :options="[
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
      <p class="q-mt-xl"><q-slider :disable="!showMiniSpectrumAnalyzer" label label-always
          :label-value="'Bar space: ' + barSpace" v-model.number="barSpace" :min="0.0" :max="1.0" :step="0.01" /></p>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">

import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { useMiniSpectrumAnalyzerSettingsStore } from "src/stores/miniSpectrumAnalyzerSettings";

const { t } = useI18n();

const miniSpectrumAnalyzerSettingsStore = useMiniSpectrumAnalyzerSettingsStore();

const showMiniSpectrumAnalyzer = computed({
  get() {
    return miniSpectrumAnalyzerSettingsStore.visible;
  },
  set(value) {
    miniSpectrumAnalyzerSettingsStore.setVisibility(value);
  }
});

const showPeaks = computed({
  get() {
    return (miniSpectrumAnalyzerSettingsStore.showPeaks);
  },
  set(value) {
    miniSpectrumAnalyzerSettingsStore.setPeaksVisibility(value);
  }
});

const ledBars = computed({
  get() {
    return (miniSpectrumAnalyzerSettingsStore.ledBarsActive);
  },
  set(value) {
    miniSpectrumAnalyzerSettingsStore.setLedBars(value);
  }
});

const trueLeds = computed({
  get() {
    return (miniSpectrumAnalyzerSettingsStore.trueLedsActive);
  },
  set(value) {
    miniSpectrumAnalyzerSettingsStore.setTrueLeds(value);
  }
});

const loRes = computed({
  get() {
    return (miniSpectrumAnalyzerSettingsStore.isLoResActive);
  },
  set(value) {
    miniSpectrumAnalyzerSettingsStore.setLoRes(value);
  }
});

const channelLayout = computed({
  get() {
    return (miniSpectrumAnalyzerSettingsStore.currentChannelLayout);
  },
  set(value) {
    miniSpectrumAnalyzerSettingsStore.setChannelLayout(value);
  }
});

const fps = computed({
  get() {
    return (miniSpectrumAnalyzerSettingsStore.currentFPS);
  },
  set(value) {
    miniSpectrumAnalyzerSettingsStore.setFPS(value);
  }
});

const mode = computed({
  get() {
    return (miniSpectrumAnalyzerSettingsStore.currentMode);
  },
  set(value) {
    miniSpectrumAnalyzerSettingsStore.setMode(value);
  }
});

const barSpace = computed({
  get() {
    return (miniSpectrumAnalyzerSettingsStore.currentBarSpace);
  },
  set(value) {
    miniSpectrumAnalyzerSettingsStore.setBarSpace(value);
  }
});

const height = computed({
  get() {
    return (miniSpectrumAnalyzerSettingsStore.height);
  },
  set(value) {
    miniSpectrumAnalyzerSettingsStore.setHeight(value);
  }
});

const colorMode = computed({
  get() {
    return (miniSpectrumAnalyzerSettingsStore.currentColorMode);
  },
  set(value) {
    miniSpectrumAnalyzerSettingsStore.setColorMode(value);
  }
});

const gradient = computed({
  get() {
    return (miniSpectrumAnalyzerSettingsStore.currentGradient);
  },
  set(value) {
    miniSpectrumAnalyzerSettingsStore.setGradient(value);
  }
});

</script>
