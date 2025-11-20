<template>
  <q-page>
    <div class="my-profile-header-container">
      <div class="my-profile-header-background-image-cover flex flex-center q-mx-auto">
        <q-avatar icon="edit" size="48px" class="bg-dark text-white my-profile-header-top-right-icon" />
        <h2 class="text-h2 text-white text-weight-bolder q-my-none">{{ t("My profile") }}</h2>
      </div>
      <div class="text-center">
        <q-avatar icon="account_circle" size="160px" class="bg-grey-4 q-mx-auto my-profile-header-center-icon" />
      </div>
    </div>
    <div class="row q-col-gutter-sm">
      <div class="col-lg-4 col-xl-4 col-12 flex">
        <UpdateProfileForm></UpdateProfileForm>
      </div>
      <div class="col-lg-4 col-xl-4 col-12 flex">
        <q-card class="full-width">
          <q-item class="theme-default-q-card-section-header">
            Mini Spectrum Analyzer Settings
          </q-item>
          <q-separator />
          <q-card-section>
            <p>
              <q-toggle v-model="showMiniSpectrumAnalyzer" label="show mini spectrum analyzer"
                @update:model-value="onChangeShowMiniSpectrumAnalyzer" />
            </p>
            <p>
              <q-toggle v-model="showPeaks" label="show peaks" @update:model-value="onChangeShowPeaks" />
              <q-toggle v-model="ledBars" label="led bars" @update:model-value="onChangeLedBars" />
              <q-toggle v-model="trueLeds" label="true leds" :disable="!ledBars"
                @update:model-value="onChangeTrueLeds" />
            </p>
            <p class="q-mt-lg"><q-slider label label-always :label-value="'Height: ' + height + 'px'" v-model="height"
                :min="30" :max="180" :step="1" v-on:update:model-value="onChangeHeight" /></p>
            <p><q-btn-toggle size="md" v-model="channelLayout" v-on:update:model-value="onChangeChannelLayout"
                toggle-color="primary" no-caps :options="[
                  { label: 'Single channel', value: 'single' },
                  { label: 'Dual channel (overlay)', value: 'dual-combined' },
                  { label: 'Dual channel (side by side)', value: 'dual-horizontal' },
                  { label: 'Dual channel (top/bottom)', value: 'dual-vertical' },
                ]" /></p>
            <p><q-btn-toggle size="md" v-model="fps" v-on:update:model-value="onChangeFPS" toggle-color="primary"
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
            <p><q-btn-toggle size="md" v-model="mode" v-on:update:model-value="onChangeMode" toggle-color="primary"
                no-caps :options="[
                  { label: '240 bands', value: 1 },
                  { label: '120 bands', value: 2 },
                  { label: '80 bands', value: 3 },
                  { label: '60 bands', value: 4 },
                  { label: '40 bands', value: 5 },
                  { label: '30 bands', value: 6 },
                  { label: '20 bands', value: 7 },
                  { label: '10 bands', value: 8 },
                ]" /></p>
            <p class="q-mt-xl"><q-slider label label-always :label-value="'Bar space: ' + barSpace" v-model="barSpace"
                :min="0.0" :max="1.0" :step="0.01" v-on:update:model-value="onChangeBarSpace" /></p>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>

import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";


import { default as UpdateProfileForm } from "src/components/Forms/UpdateProfileForm.vue";

import { useMiniSpectrumAnalyzerSettingsStore } from "src/stores/miniSpectrumAnalyzerSettings";

const { t } = useI18n();

const miniSpectrumAnalyzerSettings = useMiniSpectrumAnalyzerSettingsStore();

const showMiniSpectrumAnalyzer = ref(miniSpectrumAnalyzerSettings.visible);

const showPeaks = ref(miniSpectrumAnalyzerSettings.showPeaks);

const ledBars = ref(miniSpectrumAnalyzerSettings.ledBarsActive);

const trueLeds = ref(miniSpectrumAnalyzerSettings.trueLedsActive);

const channelLayout = ref(miniSpectrumAnalyzerSettings.currentChannelLayout);

const fps = ref(miniSpectrumAnalyzerSettings.currentFPS);

const mode = ref(miniSpectrumAnalyzerSettings.currentMode);

const barSpace = ref(miniSpectrumAnalyzerSettings.currentBarSpace);

const height = ref(miniSpectrumAnalyzerSettings.height);

watch(() => miniSpectrumAnalyzerSettings.visible, (newValue) => {
  showMiniSpectrumAnalyzer.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettings.showPeaks, (newValue) => {
  showPeaks.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettings.ledBarsActive, (newValue) => {
  ledBars.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettings.trueLedsActive, (newValue) => {
  trueLeds.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettings.currentFPS, (newValue) => {
  fps.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettings.currentMode, (newValue,) => {
  mode.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettings.currentBarSpace, (newValue) => {
  barSpace.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettings.height, (newValue) => {
  height.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettings.channelLayout, (newValue) => {
  channelLayout.value = newValue;
});

const onChangeShowMiniSpectrumAnalyzer = (visible) => {
  miniSpectrumAnalyzerSettings.setVisibility(visible);
};

const onChangeFPS = (fps) => {
  miniSpectrumAnalyzerSettings.setFPS(fps);
}

const onChangeMode = (mode) => {
  miniSpectrumAnalyzerSettings.setMode(mode);
};

const onChangeBarSpace = (space) => {
  miniSpectrumAnalyzerSettings.setBarSpace(space);
}

const onChangeHeight = (height) => {
  miniSpectrumAnalyzerSettings.setHeight(height);
};

const onChangeChannelLayout = (layout) => {
  miniSpectrumAnalyzerSettings.setChannelLayout(layout);
}

const onChangeShowPeaks = (visible) => {
  miniSpectrumAnalyzerSettings.setPeaksVisibility(visible);
};

const onChangeLedBars = (active) => {
  miniSpectrumAnalyzerSettings.setLedBars(active);
};

const onChangeTrueLeds = (active) => {
  miniSpectrumAnalyzerSettings.setTrueLeds(active);
};


</script>

<style lang="css" scoped>
.my-profile-header-container {
  height: 350px;
  margin-bottom: 1em;
}

.my-profile-header-background-image-cover {
  width: 100%;
  height: 260px;
  /* image credits: https://www.pexels.com/photo/keyboard-and-mouse-on-beige-background-3184460/ */
  background-image: linear-gradient(135deg, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.8) 100%), url('/images/pexels-photo-3184460.jpg');
  background-size: cover;
  background-position: bottom;
  filter: grayscale(100%);
  border-radius: 16px;
}

.my-profile-header-top-right-icon {
  position: absolute;
  top: 16px;
  right: 16px;
  color: blue;
}

.my-profile-header-center-icon {
  position: relative;
  top: -80px;
}
</style>