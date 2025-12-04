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
              <q-toggle v-model="showMiniSpectrumAnalyzer" label="visible"
                @update:model-value="onChangeShowMiniSpectrumAnalyzer" />
              <q-toggle v-model="loRes" :disable="!showMiniSpectrumAnalyzer" label="Low resolution"
                @update:model-value="onChangeLoRes" />
            </p>
            <p>
              <q-toggle v-model="showPeaks" :disable="!showMiniSpectrumAnalyzer" label="show peaks"
                @update:model-value="onChangeShowPeaks" />
              <q-toggle v-model="ledBars" :disable="!showMiniSpectrumAnalyzer" label="led bars"
                @update:model-value="onChangeLedBars" />
              <q-toggle v-model="trueLeds" :disable="!showMiniSpectrumAnalyzer || !ledBars" label="true leds"
                @update:model-value="onChangeTrueLeds" />

            </p>
            <p class="q-mt-lg"><q-slider :disable="!showMiniSpectrumAnalyzer" label label-always
                :label-value="'Height: ' + height + 'px'" v-model="height" :min="30" :max="180" :step="1"
                v-on:update:model-value="onChangeHeight" /></p>
            <p><q-btn-toggle spread size="md" :disable="!showMiniSpectrumAnalyzer" v-model="gradient"
                v-on:update:model-value="onChangeGradient" toggle-color="primary" no-caps :options="[
                  { label: 'spieldose', value: 'spieldose' },
                  { label: 'classic', value: 'classic' },
                  { label: 'orangered', value: 'orangered' },
                  { label: 'prism', value: 'prism' },
                  { label: 'rainbow', value: 'rainbow' },
                  { label: 'steelblue', value: 'steelblue' },
                ]" /></p>
            <p><q-btn-toggle spread size="md" :disable="!showMiniSpectrumAnalyzer" v-model="channelLayout"
                v-on:update:model-value="onChangeChannelLayout" toggle-color="primary" no-caps :options="[
                  { label: 'Single channel', value: 'single' },
                  { label: 'Dual channel (overlay)', value: 'dual-combined' },
                  { label: 'Dual channel (side by side)', value: 'dual-horizontal' },
                  { label: 'Dual channel (top/bottom)', value: 'dual-vertical' },
                ]" /></p>
            <p><q-btn-toggle spread size="md" :disable="!showMiniSpectrumAnalyzer" v-model="fps"
                v-on:update:model-value="onChangeFPS" toggle-color="primary" no-caps :options="[
                  { label: '10fps', value: 10 },
                  { label: '15fps', value: 15 },
                  { label: '30fps', value: 30 },
                  { label: '60fps', value: 60 },
                  { label: '90fps', value: 90 },
                  { label: '120fps', value: 120 },
                  { label: '144fps', value: 144 },
                  { label: 'unlimited fps', value: 0 },
                ]" /></p>
            <p><q-btn-toggle spread size="md" :disable="!showMiniSpectrumAnalyzer" v-model="mode"
                v-on:update:model-value="onChangeMode" toggle-color="primary" no-caps :options="[
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
                :label-value="'Bar space: ' + barSpace" v-model.number="barSpace" :min="0.0" :max="1.0" :step="0.01"
                v-on:update:model-value="onChangeBarSpace" /></p>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">

import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { default as UpdateProfileForm } from "src/components/Forms/UpdateProfileForm.vue";
import { type SpectrumAnalyzerChannelLayout } from "src/types/common";
import { useMiniSpectrumAnalyzerSettingsStore } from "src/stores/miniSpectrumAnalyzerSettings";

const { t } = useI18n();

const miniSpectrumAnalyzerSettingsStore = useMiniSpectrumAnalyzerSettingsStore();

const showMiniSpectrumAnalyzer = ref(miniSpectrumAnalyzerSettingsStore.visible);

const showPeaks = ref(miniSpectrumAnalyzerSettingsStore.showPeaks);

const ledBars = ref(miniSpectrumAnalyzerSettingsStore.ledBarsActive);

const trueLeds = ref(miniSpectrumAnalyzerSettingsStore.trueLedsActive);

const loRes = ref(miniSpectrumAnalyzerSettingsStore.isLoResActive);

const channelLayout = ref(miniSpectrumAnalyzerSettingsStore.currentChannelLayout);

const fps = ref(miniSpectrumAnalyzerSettingsStore.currentFPS);

const mode = ref(miniSpectrumAnalyzerSettingsStore.currentMode);

const barSpace = ref(miniSpectrumAnalyzerSettingsStore.currentBarSpace);

const height = ref(miniSpectrumAnalyzerSettingsStore.height);

const gradient = ref(miniSpectrumAnalyzerSettingsStore.currentGradient);

watch(() => miniSpectrumAnalyzerSettingsStore.visible, (newValue) => {
  showMiniSpectrumAnalyzer.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettingsStore.showPeaks, (newValue) => {
  showPeaks.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettingsStore.ledBarsActive, (newValue) => {
  ledBars.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettingsStore.trueLedsActive, (newValue) => {
  trueLeds.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettingsStore.isLoResActive, (newValue) => {
  loRes.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettingsStore.currentFPS, (newValue) => {
  fps.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettingsStore.currentMode, (newValue,) => {
  mode.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettingsStore.currentBarSpace, (newValue) => {
  barSpace.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettingsStore.height, (newValue) => {
  height.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettingsStore.channelLayout, (newValue) => {
  channelLayout.value = newValue;
});

watch(() => miniSpectrumAnalyzerSettingsStore.gradient, (newValue) => {
  gradient.value = newValue;
});

const onChangeShowMiniSpectrumAnalyzer = (visible: boolean) => {
  miniSpectrumAnalyzerSettingsStore.setVisibility(visible);
};

const onChangeFPS = (fps: number) => {
  miniSpectrumAnalyzerSettingsStore.setFPS(fps);
}

const onChangeMode = (mode: number) => {
  miniSpectrumAnalyzerSettingsStore.setMode(mode);
};

const onChangeBarSpace = (space: number | null) => {
  miniSpectrumAnalyzerSettingsStore.setBarSpace(space || 40);
}

const onChangeHeight = (height: number | null) => {
  miniSpectrumAnalyzerSettingsStore.setHeight(height || 0);
};

const onChangeChannelLayout = (layout: SpectrumAnalyzerChannelLayout) => {
  miniSpectrumAnalyzerSettingsStore.setChannelLayout(layout);
}

const onChangeGradient = (gradient: string) => {
  miniSpectrumAnalyzerSettingsStore.setGradient(gradient);
}

const onChangeShowPeaks = (visible: boolean) => {
  miniSpectrumAnalyzerSettingsStore.setPeaksVisibility(visible);
};

const onChangeLedBars = (active: boolean) => {
  miniSpectrumAnalyzerSettingsStore.setLedBars(active);
};

const onChangeTrueLeds = (active: boolean) => {
  miniSpectrumAnalyzerSettingsStore.setTrueLeds(active);
};

const onChangeLoRes = (active: boolean) => {
  miniSpectrumAnalyzerSettingsStore.setLoRes(active);
}

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