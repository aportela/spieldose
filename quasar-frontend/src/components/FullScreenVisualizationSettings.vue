<template>
  <div>
    <div id="visualization-container" style="background-image: url('images/overlay.jpg');">
      <div id="analyzer-canvas-container"></div>
      <q-card id="settings-container" class="bg-grey-4 q-pb-lg"
        :class="{ 'fixed-center': settings.fullScreen, 'q-mx-auto q-mt-lg': !settings.fullScreen }" v-if="showSettings">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Spieldose analyzer settings </div>
          <q-space />
          <q-btn icon="close" flat round dense class="cursor-pointer" @click="showSettings = false;" />
        </q-card-section>
        <q-card-section>
          <div class="row">
            <div class="col-12">
              <q-item-label header class="text-dark text-weight-bolder">Gradient</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-btn-toggle dense v-model="settings.gradient" unelevated toggle-color="pink" :options="gradientValues"
                    @update:model-value="(v) => onSet('gradient', v)" no-caps />
                  <!--
                  <div>
                    <q-btn class="q-ma-xs" unelevated dense size="md" :color="settings.gradient == gg.value ? 'pink': 'dark'" v-for="gg in gradientValues" :key="gg.value" :label="gg.label"  @click="onSet('gradient', gg.value)" no-caps></q-btn>
                  </div>
                  -->
                </q-item-section>
              </q-item>
            </div>
          </div>
          <div class="row q-pb-lg">
            <div class="col-2">
              <q-item-label header>Max FPS</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-slider v-model="settings.maxFPS" :min="0" :max="120" :step="30" label label-always switch-label-side
                    :label-value="selectedmaxFPSLabel" color="grey" @change="onSet('maxFPS', settings.maxFPS)" />
                </q-item-section>
              </q-item>
            </div>
            <div class="col-2">
              <q-item-label header>Show FPS</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-btn-toggle dense size="sm" v-model="settings.showFPS" unelevated toggle-color="pink" spread :options="[
                    { label: 'ON', value: true },
                    { label: 'OFF', value: false }
                  ]" @update:model-value="(v) => onSet('showFPS', v)" />
                </q-item-section>
              </q-item>
            </div>
            <div class="col-2">
              <q-item-label header>Analyzer mode</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-slider dense v-model="settings.mode" :min="0" :max="10" :step="1" label label-always
                    switch-label-side :label-value="selectedModeLabel" color="grey"
                    @change="onSet('mode', settings.mode)" />
                </q-item-section>
              </q-item>
            </div>
            <div class="col-2">
              <q-item-label header>Channel Layout</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-btn-toggle dense size="sm" v-model="settings.channelLayout" unelevated toggle-color="pink" spread
                    :options="[
                      { label: 'mono', value: 'single' },
                      { label: 'stereo', value: 'dual-vertical' }
                    ]" @update:model-value="(v) => onSet('channelLayout', v)" />
                </q-item-section>
              </q-item>
            </div>
            <div class="col-2">
              <q-item-label header>Radial</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-btn-toggle dense size="sm" v-model="settings.radial" unelevated toggle-color="pink" spread :options="[
                    { label: 'ON', value: true },
                    { label: 'OFF', value: false }
                  ]" @update:model-value="(v) => onSet('radial', v)" />
                </q-item-section>
              </q-item>
            </div>
            <div class="col-2">
              <q-item-label header>Peaks</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-btn-toggle dense size="sm" v-model="settings.showPeaks" unelevated toggle-color="pink" spread
                    :options="[
                      { label: 'ON', value: true },
                      { label: 'OFF', value: false }
                    ]" @update:model-value="(v) => onSet('showPeaks', v)" :disable="settings.lumiBars" />
                </q-item-section>
              </q-item>
            </div>
          </div>
          <q-item-label header class="q-pb-none text-dark text-weight-bolder">Mirror & reflex settings</q-item-label>
          <div class="row q-pb-lg">
            <div class="col-3">
              <q-item-label header>Horizontal mirror</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-btn-toggle size="sm" v-model="settings.mirror" unelevated no-caps toggle-color="pink" spread
                    :options="[
                      { label: 'Left', value: -1 },
                      { label: 'None', value: 0 },
                      { label: 'Right', value: 1 }
                    ]" @update:model-value="(v) => onSet('mirror', v)" />
                </q-item-section>
              </q-item>
            </div>
            <div class="col-3">
              <q-item-label header>Vertical reflex ratio</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-slider v-model="settings.reflexRatio" :min="0" :max="0.9" :step="0.1" label label-always
                    switch-label-side :label-value="selectedReflexRatioLabel" color="grey"
                    @change="onSet('reflexRatio', settings.reflexRatio)" />
                </q-item-section>
              </q-item>
            </div>
            <div class="col-3">
              <q-item-label header>Vertical reflex alpha</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-slider v-model="settings.reflexAlpha" :min="0" :max="1" :step="0.1" label label-always
                    switch-label-side color="grey" @change="onSet('reflexAlpha', settings.reflexAlpha)" />
                </q-item-section>
              </q-item>
            </div>
            <div class="col-3">
              <q-item-label header>Vertical reflex bright</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-slider v-model="settings.reflexBright" :min="0" :max="1" :step="0.1" label label-always
                    switch-label-side color="grey" @change="onSet('reflexBright', settings.reflexBright)" />
                </q-item-section>
              </q-item>
            </div>
          </div>
          <div v-if="settings.mode > 0 && settings.mode < 9">
            <q-item-label header class="text-dark text-weight-bolder">Octave bar settings</q-item-label>
            <div class="row">
              <div class="col-4">
                <q-item-label header>Bar space</q-item-label>
                <q-item dense>
                  <q-item-section>
                    <q-slider v-model="settings.barSpace" :min="0" :max="0.9" :step="0.1" label label-always
                      switch-label-side color="grey" @change="onSet('barSpace', settings.barSpace)" />
                  </q-item-section>
                </q-item>
              </div>
              <div class="col-2">
                <q-item-label header>Led bars</q-item-label>
                <q-item dense>
                  <q-item-section>
                    <q-btn-toggle size="sm" v-model="settings.ledBars" unelevated toggle-color="pink" spread :options="[
                      { label: 'ON', value: true },
                      { label: 'OFF', value: false }
                    ]" @update:model-value="(v) => onSet('ledBars', v)" />
                  </q-item-section>
                </q-item>
              </div>
              <div class="col-2">
                <q-item-label header>True leds</q-item-label>
                <q-item dense>
                  <q-item-section>
                    <q-btn-toggle size="sm" v-model="settings.trueLeds" unelevated toggle-color="pink" spread :options="[
                      { label: 'ON', value: true },
                      { label: 'OFF', value: false }
                    ]" @update:model-value="(v) => onSet('trueLeds', v)" />
                  </q-item-section>
                </q-item>
              </div>
              <div class="col-2">
                <q-item-label header>Lumi bars</q-item-label>
                <q-item dense>
                  <q-item-section>
                    <q-btn-toggle size="sm" v-model="settings.lumiBars" unelevated toggle-color="pink" spread :options="[
                      { label: 'ON', value: true },
                      { label: 'OFF', value: false }
                    ]" @update:model-value="(v) => onSet('lumiBars', v)" />
                  </q-item-section>
                </q-item>
              </div>
              <div class="col-2">
                <q-item-label header>Alpha bars</q-item-label>
                <q-item dense>
                  <q-item-section>
                    <q-btn-toggle dense size="sm" v-model="settings.alphaBars" unelevated toggle-color="pink" spread
                      :options="[
                        { label: 'ON', value: true },
                        { label: 'OFF', value: false }
                      ]" @update:model-value="(v) => onSet('alphaBars', v)" />
                  </q-item-section>
                </q-item>
              </div>
            </div>
            <div>
              <q-item-label header class="text-dark text-weight-bolder">Analyzer canvas height</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-slider v-model="settings.height" :min="100" :max="maxCanvasHeight" :step="25" label label-always
                    switch-label-side color="grey" @change="onSet('height', settings.height)" />
                </q-item-section>
              </q-item>
            </div>
          </div>
        </q-card-section>
      </q-card>
      <div class="fixed-bottom-right">
        <q-icon id="showVisualizationSettingsIcon" name="settings" color="white" size="xs" class="cursor-pointer q-ma-xs"
          @click="showSettings = true"></q-icon>
      </div>
    </div>
  </div>
</template>

<style scoped>
div#visualization-container {
  width: 100%;
  height: fit-content;
  background-color: #000;
  background-repeat: no-repeat;
  background-position: 50%;
  background-size: auto 150%;
}

div#analyzer-canvas-container {
  width: 100%;
  height: fit-content;
}

#settings-container {
  width: 50%;
  max-width: 1024px;
  opacity: 0.9;
}

i#showVisualizationSettingsIcon {
  opacity: 0.2;
  transition: 0.3s;
}
i#showVisualizationSettingsIcon:hover {
  opacity: 1;
}
</style>

<script setup>

import { useQuasar } from "quasar";
import { bus } from "boot/bus";
import { useSessionStore } from "stores/session";

const $q = useQuasar();

const session = useSessionStore();
if (!session.isLoaded) {
  session.load();
}

const maxCanvasHeight = Math.round($q.screen.height / 2);

import { ref, computed, onMounted } from "vue";
import AudioMotionAnalyzer from 'audiomotion-analyzer';
import { usePlayer } from "stores/player";

const showSettings = ref(false);
const player = usePlayer();
const audioElement = ref(player.getElement);

// taken from https://github.com/hvianna/audioMotion.js/blob/master/src/index.js
const staticGradients = {
  apple: {
    name: 'Apple ][', colorStops: [
      { pos: .1667, color: '#61bb46' },
      { pos: .3333, color: '#fdb827' },
      { pos: .5, color: '#f5821f' },
      { pos: .6667, color: '#e03a3e' },
      { pos: .8333, color: '#963d97' },
      { pos: 1, color: '#009ddc' }
    ], disabled: false
  },
  aurora: {
    name: 'Aurora', bgColor: '#0e172a', colorStops: [
      { pos: .1, color: 'hsl( 120, 100%, 50% )' },
      { pos: 1, color: 'hsl( 216, 100%, 50% )' }
    ], disabled: false
  },
  borealis: {
    name: 'Borealis', bgColor: '#0d1526', colorStops: [
      { pos: .1, color: 'hsl( 120, 100%, 50% )' },
      { pos: .5, color: 'hsl( 189, 100%, 40% )' },
      { pos: 1, color: 'hsl( 290, 60%, 40% )' }
    ], disabled: false
  },
  candy: {
    name: 'Candy', bgColor: '#0d0619', colorStops: [
      { pos: .1, color: '#ffaf7b' },
      { pos: .5, color: '#d76d77' },
      { pos: 1, color: '#3a1c71' }
    ], disabled: false
  },
  cool: {
    name: 'Cool', bgColor: '#0b202b', colorStops: [
      'hsl( 208, 0%, 100% )',
      'hsl( 208, 100%, 35% )'
    ], disabled: false
  },
  dusk: {
    name: 'Dusk', bgColor: '#0e172a', colorStops: [
      { pos: .2, color: 'hsl( 55, 100%, 50% )' },
      { pos: 1, color: 'hsl( 16, 100%, 50% )' }
    ], disabled: false
  },
  miami: {
    name: 'Miami', bgColor: '#110a11', colorStops: [
      { pos: .024, color: 'rgb( 251, 198, 6 )' },
      { pos: .283, color: 'rgb( 224, 82, 95 )' },
      { pos: .462, color: 'rgb( 194, 78, 154 )' },
      { pos: .794, color: 'rgb( 32, 173, 190 )' },
      { pos: 1, color: 'rgb( 22, 158, 95 )' }
    ], disabled: false
  },
  orient: {
    name: 'Orient', bgColor: '#100', colorStops: [
      { pos: .1, color: '#f00' },
      { pos: 1, color: '#600' }
    ], disabled: false
  },
  outrun: {
    name: 'Outrun', bgColor: '#101', colorStops: [
      { pos: 0, color: 'rgb( 255, 223, 67 )' },
      { pos: .182, color: 'rgb( 250, 84, 118 )' },
      { pos: .364, color: 'rgb( 198, 59, 243 )' },
      { pos: .525, color: 'rgb( 133, 80, 255 )' },
      { pos: .688, color: 'rgb( 74, 104, 247 )' },
      { pos: 1, color: 'rgb( 35, 210, 255 )' }
    ], disabled: false
  },
  pacific: {
    name: 'Pacific Dream', bgColor: '#051319', colorStops: [
      { pos: .1, color: '#34e89e' },
      { pos: 1, color: '#0f3443' }
    ], disabled: false
  },
  shahabi: {
    name: 'Shahabi', bgColor: '#060613', colorStops: [
      { pos: .1, color: '#66ff00' },
      { pos: 1, color: '#a80077' }
    ], disabled: false
  },
  summer: {
    name: 'Summer', bgColor: '#041919', colorStops: [
      { pos: .1, color: '#fdbb2d' },
      { pos: 1, color: '#22c1c3' }
    ], disabled: false
  },
  sunset: {
    name: 'Sunset', bgColor: '#021119', colorStops: [
      { pos: .1, color: '#f56217' },
      { pos: 1, color: '#0b486b' }
    ], disabled: false
  },
  tiedye: {
    name: 'Tie Dye', colorStops: [
      { pos: .038, color: 'rgb( 15, 209, 165 )' },
      { pos: .208, color: 'rgb( 15, 157, 209 )' },
      { pos: .519, color: 'rgb( 133, 13, 230 )' },
      { pos: .731, color: 'rgb( 230, 13, 202 )' },
      { pos: .941, color: 'rgb( 242, 180, 107 )' }
    ], disabled: false
  }
};

const gradientValues = ref([
  {
    label: 'classic',
    value: 'classic',
  },
  {
    label: 'orangered',
    value: 'orangered',
  },
  {
    label: 'prism',
    value: 'prism',
  },
  {
    label: 'rainbow',
    value: 'rainbow',
  },
  {
    label: 'steelblue',
    value: 'steelblue',
  }
]);

for (const [key] of Object.entries(staticGradients)) {
  gradientValues.value.push({ label: key, value: key });
}

const modes = {
  0: 'Discrete frequencies',
  1: '1/24th octave bands',
  2: '1/12th octave bands',
  3: '1/8th octave bands',
  4: '1/6th octave bands',
  5: '1/4th octave bands',
  6: '1/3rd octave bands',
  7: 'Half octave bands',
  8: 'Full octave bands',
  9: 'Line / Area graph'
};

const selectedModeLabel = computed(() => {
  return (modes[settings.value.mode]);
});

const selectedmaxFPSLabel = computed(() => {
  if (settings.value.maxFPS != 0) {
    return (settings.value.maxFPS);
  } else {
    return ('unlimited');
  }
});

const selectedReflexRatioLabel = computed(() => {
  if (settings.value.reflexRatio != 0) {
    return (settings.value.reflexRatio);

  } else {
    return ('no reflection');
  }
});

const analyzer = ref(null);

const defaultSettings = {
  source: audioElement.value,
  connectSpeakers: false,
  fsElement: null,
  gradient: 'classic',
  fullScreen: false,
  mode: 2,
  mirror: 0,
  height: 625,
  barSpace: 0.6,
  maxFPS: 90,
  showFPS: false,
  radial: false,
  ledBars: true,
  trueLeds: false,
  lumiBars: false,
  alphaBars: false,
  showPeaks: true,
  channelLayout: 'single',
  reflexRatio: 0.3,
  reflexAlpha: 0.2,
  reflexBright: 0.8,
  overlay: true,
  showBgColor: true,
  bgAlpha: 0.5,
  showScaleX: false,
  showScaleY: false,
  splitGradient: false,
  start: false,
  reflexFit: true,
  onCanvasResize: (reason, instance) => {
    if (reason == 'fschange') {
      settings.value.fullScreen = instance.isFullscreen;
      if (!instance.isFullscreen) {
        if (analyzer.value) {
          analyzer.value.destroy();
        }
        bus.emit('hideFullScreenVisualization');
      }
    }
  }
};

const settings = ref(session.getFullScreenVisualizationSettings || defaultSettings);

function onSet(optionName, optionValue) {
  const option = {};
  option[optionName] = optionValue;
  analyzer.value.setOptions(option);
  session.saveFullScreenVisualizationSettings(settings.value);
}

function createAnalyzer() {
  if (!analyzer.value) {
    analyzer.value = new AudioMotionAnalyzer(
      document.getElementById('analyzer-canvas-container'),
      settings.value
    );
    for (const [key, value] of Object.entries(staticGradients)) {
      analyzer.value.registerGradient(key, { bgColor: value.bgColor || '#111', colorStops: value.colorStops || [] });
    }
  }
  if (!analyzer.value.isOn) {
    analyzer.value.toggleAnalyzer();
    analyzer.value.toggleFullscreen();
  }
}

onMounted(() => {
  settings.value.fsElement = document.getElementById('visualization-container');
  createAnalyzer();
});
</script>
