<template>
  <div>
    <div id="visualization-container" style="background-image: url('images/overlay.jpg');">
      <div id="analyzer-canvas-container"></div>
      <q-card id="settings-container" class="bg-grey-4 q-pb-lg fixed-center" v-if="showSettings">
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
                  <q-btn-toggle dense v-model="settings.audioMotionAnalyzer.gradient" unelevated toggle-color="pink" :options="gradientValues"
                    @update:model-value="(v) => onSet('gradient', v)" no-caps />
                  <!--
                  <div>
                    <q-btn class="q-ma-xs" unelevated dense size="md" :color="settings.audioMotionAnalyzer.gradient == gg.value ? 'pink': 'dark'" v-for="gg in gradientValues" :key="gg.value" :label="gg.label"  @click="onSet('gradient', gg.value)" no-caps></q-btn>
                  </div>
                  -->
                </q-item-section>
              </q-item>
            </div>
          </div>
          <q-item-label header class="text-dark text-weight-bolder">General</q-item-label>
          <div class="row q-pb-lg">
            <div class="col-2">
              <q-item-label header>Max FPS</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-slider v-model="settings.audioMotionAnalyzer.maxFPS" :min="0" :max="120" :step="30" label label-always switch-label-side
                    :label-value="selectedmaxFPSLabel" color="grey" @change="onSet('maxFPS', settings.audioMotionAnalyzer.maxFPS)" />
                </q-item-section>
              </q-item>
            </div>
            <div class="col-2">
              <q-item-label header>Show FPS</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-btn-toggle dense size="sm" v-model="settings.audioMotionAnalyzer.showFPS" unelevated toggle-color="pink" spread :options="[
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
                  <q-slider dense v-model="settings.audioMotionAnalyzer.mode" :min="0" :max="10" :step="1" label label-always
                    switch-label-side :label-value="selectedModeLabel" color="grey"
                    @change="onSet('mode', settings.audioMotionAnalyzer.mode)" />
                </q-item-section>
              </q-item>
            </div>
            <div class="col-2">
              <q-item-label header>Channel Layout</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-btn-toggle dense size="sm" v-model="settings.audioMotionAnalyzer.channelLayout" unelevated toggle-color="pink" spread
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
                  <q-btn-toggle dense size="sm" v-model="settings.audioMotionAnalyzer.radial" unelevated toggle-color="pink" spread :options="[
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
                  <q-btn-toggle dense size="sm" v-model="settings.audioMotionAnalyzer.showPeaks" unelevated toggle-color="pink" spread
                    :options="[
                      { label: 'ON', value: true },
                      { label: 'OFF', value: false }
                    ]" @update:model-value="(v) => onSet('showPeaks', v)" :disable="settings.audioMotionAnalyzer.lumiBars" />
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
                  <q-btn-toggle size="sm" v-model="settings.audioMotionAnalyzer.mirror" unelevated no-caps toggle-color="pink" spread
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
                  <q-slider v-model="settings.audioMotionAnalyzer.reflexRatio" :min="0" :max="0.9" :step="0.1" label label-always
                    switch-label-side :label-value="selectedReflexRatioLabel" color="grey"
                    @change="onSet('reflexRatio', settings.audioMotionAnalyzer.reflexRatio)" />
                </q-item-section>
              </q-item>
            </div>
            <div class="col-3">
              <q-item-label header>Vertical reflex alpha</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-slider v-model="settings.audioMotionAnalyzer.reflexAlpha" :min="0" :max="1" :step="0.1" label label-always
                    switch-label-side color="grey" @change="onSet('reflexAlpha', settings.audioMotionAnalyzer.reflexAlpha)" />
                </q-item-section>
              </q-item>
            </div>
            <div class="col-3">
              <q-item-label header>Vertical reflex bright</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-slider v-model="settings.audioMotionAnalyzer.reflexBright" :min="0" :max="1" :step="0.1" label label-always
                    switch-label-side color="grey" @change="onSet('reflexBright', settings.audioMotionAnalyzer.reflexBright)" />
                </q-item-section>
              </q-item>
            </div>
          </div>
          <div v-if="settings.audioMotionAnalyzer.mode > 0 && settings.audioMotionAnalyzer.mode < 9">
            <q-item-label header class="text-dark text-weight-bolder">Octave bar settings</q-item-label>
            <div class="row">
              <div class="col-4">
                <q-item-label header>Bar space</q-item-label>
                <q-item dense>
                  <q-item-section>
                    <q-slider v-model="settings.audioMotionAnalyzer.barSpace" :min="0" :max="0.9" :step="0.1" label label-always
                      switch-label-side color="grey" @change="onSet('barSpace', settings.audioMotionAnalyzer.barSpace)" />
                  </q-item-section>
                </q-item>
              </div>
              <div class="col-2">
                <q-item-label header>Led bars</q-item-label>
                <q-item dense>
                  <q-item-section>
                    <q-btn-toggle size="sm" v-model="settings.audioMotionAnalyzer.ledBars" unelevated toggle-color="pink" spread :options="[
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
                    <q-btn-toggle size="sm" v-model="settings.audioMotionAnalyzer.trueLeds" unelevated toggle-color="pink" spread :options="[
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
                    <q-btn-toggle size="sm" v-model="settings.audioMotionAnalyzer.lumiBars" unelevated toggle-color="pink" spread :options="[
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
                    <q-btn-toggle dense size="sm" v-model="settings.audioMotionAnalyzer.alphaBars" unelevated toggle-color="pink" spread
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
                  <q-slider v-model="settings.audioMotionAnalyzer.height" :min="100" :max="maxCanvasHeight" :step="25" label label-always
                    switch-label-side color="grey" @change="onSet('height', settings.audioMotionAnalyzer.height)" />
                </q-item-section>
              </q-item>
            </div>
          </div>
        </q-card-section>
      </q-card>


      <div class="row">
        <div class="col-6">
          <div style="margin: 64px;">
            <q-img img-class="fullscreen_album_cover shadow-18" v-if="coverImage" :src="coverImage" width="400px"
              height="400px" spinner-color="pink">
              <template v-slot:error>
                <q-img src="images/vinyl.png" width="400px" height="400px"></q-img>
              </template>
            </q-img>
            <q-img v-else src="images/vinyl.png" width="400px" height="400px"></q-img>
          </div>
          <div class="q-px-md" style="margin-left:64px;" v-if="currentElement && currentElement.track">
            <h2 class="text-grey-2 q-mt-none q-mb-sm"><q-icon name="music_note" size="xl" class="q-mr-sm"></q-icon>{{
              currentElement.track.title || null }}</h2>
            <h4 class="text-grey-5 q-mt-md q-mb-sm" v-if="currentElement.track.artist.name">by {{
              currentElement.track.artist.name }}
            </h4>
            <h4 class="text-grey-6 q-mt-xs"><q-icon name="album" size="xl" class="q-mr-sm"></q-icon>{{
              currentElement.track.album.title }} <span v-if="currentElement.track.album.year">({{
    currentElement.track.album.year }})</span></h4>
          </div>
        </div>
        <div class="col-3 self-end">
          <div>
            <q-btn round dense size="30px" color="dark" style="opacity: 0.8" :disable="disabled"
              @click="onSkipPrevious"><q-icon name="skip_previous" title="Skip to previous track"></q-icon></q-btn>
            <q-btn round dense class="q-mx-lg" size="60px" color="dark" style="opacity: 0.8"
              :disable="disabled" @click="onPlay"><q-icon :name="playerStatus.isPlaying ? 'pause' : 'play_arrow'"
                :class="{ 'text-pink-6': playerStatus.isPlaying }" title="Play/Pause/Resume track"></q-icon></q-btn>
            <q-btn round dense size="30px" color="dark" style="opacity: 0.8" :disable="disabled"
              @click="onSkipNext"><q-icon name="skip_next" title="Skip to next track"></q-icon></q-btn>
          </div>
          <!--
              <h3 class="text-grey-5 q-ml-lg q-mb-sm">{{ formatSecondsAsTime(currentElementTimeData.currentTime) }} / {{
                formatSecondsAsTime(currentElementTimeData.duration) }} </h3>
                -->
          <h4 class="text-grey-6 q-ml-lg q-mt-sm">Track {{ currentTrackIndex || 0 }} of {{ totalTracks || 0 }}</h4>
        </div>
        <div class="col-3 self-center">
          <q-list dark bordered separator style="max-width: 400px; background: #000; opacity: 0.8">
            <q-item>
              <q-item-section>
                <q-item-label>Currently playing</q-item-label>
              </q-item-section>
              <q-item-section side top>
                <q-item-label caption>{{ currentTrackIndex || 0 }} of {{ totalTracks || 0 }}</q-item-label>
              </q-item-section>
            </q-item>
            <q-item v-if="currentElement && currentElement.track">
              <q-item-section avatar>
                <q-avatar>
                  <q-img :src="currentElement.track.covers.small" v-if="currentElement.track.covers.small">
                    <template v-slot:error>
                      <q-icon name="album"></q-icon>
                    </template>
                  </q-img>
                  <q-icon name="album" v-else></q-icon>
                </q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ currentElement.track.title }}</q-item-label>
                <q-item-label caption>{{ currentElement.track.artist.name }}</q-item-label>
              </q-item-section>
            </q-item>
            <q-item>
              <q-item-section>
                <q-item-label overline>Playlist</q-item-label>
                <q-item-label label>Current playlist</q-item-label>
              </q-item-section>
              <q-item-section side top>
                <q-item-label caption>{{ totalTracks }} total tracks</q-item-label>
              </q-item-section>
            </q-item>
            <q-separator spaced />
            <q-virtual-scroll style="height: 400px; max-width: 400px;" dark visible separator
              :items="currentPlaylist.getElements" v-slot="{ item, index }">
              <q-item clickable :key="item.id" @click="onSetCurrentIndex(index)">
                <q-item-section avatar>
                  <q-avatar>
                    <q-img :src="item.track.covers.small" v-if="item.track.covers.small">
                      <template v-slot:error>
                        <q-icon name="album"></q-icon>
                      </template>
                    </q-img>
                    <q-icon name="album" v-else></q-icon>
                  </q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ item.track.title }}</q-item-label>
                  <q-item-label caption>{{ item.track.artist.name }}</q-item-label>
                </q-item-section>
              </q-item>
            </q-virtual-scroll>
          </q-list>
        </div>
      </div>



      <div class="fixed-bottom-right">
        <q-icon name="settings" color="white" size="xs" class="visualization-bottom-icons cursor-pointer q-ma-xs"
          @click="showSettings = true"></q-icon>
        <q-icon name="close" color="white" size="xs" class="visualization-bottom-icons cursor-pointer q-ma-xs"
          @click="onClose"></q-icon>
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
  width: 80em;
  max-width: 1280px;
  opacity: 0.8;
  z-index: 1;
}

i.visualization-bottom-icons {
  opacity: 0.2;
  transition: 0.3s;
}

i.visualization-bottom-icons {
  opacity: 1;
}
</style>

<script setup>

import { ref, computed, inject, onMounted } from "vue";
import { useQuasar } from "quasar";
import { bus } from "boot/bus";
import AudioMotionAnalyzer from "audiomotion-analyzer";
import { usePlayer } from "stores/player";
import { usePlayerStatusStore } from "stores/playerStatus";
import { useCurrentPlaylistStore } from "stores/currentPlaylist";
import { useSessionStore } from "stores/session";

const disabled = ref(false);

const spieldoseEvents = inject('spieldoseEvents');

const $q = useQuasar();
const session = useSessionStore();
if (!session.isLoaded) {
  session.load();
}
const maxCanvasHeight = Math.round($q.screen.height / 2);
const showSettings = ref(false);
const player = usePlayer();
const playerStatus = usePlayerStatusStore();
const currentPlaylist = useCurrentPlaylistStore();
const totalTracks = currentPlaylist.elementCount;
const currentTrackIndex = ref(currentPlaylist.getCurrentIndex);

const audioElement = ref(player.getElement);

const currentElement = computed(() => {
  return (currentPlaylist.getCurrentElement);
});

const coverImage = computed(() => {
  if (currentElement.value) {
    if (currentElement.value.track && currentElement.value.track.covers) {
      return (currentElement.value.track.covers.normal || null);
    } else if (currentElement.value.radioStation && currentElement.value.radioStation.images) {
      return (currentElement.value.radioStation.images.normal || null);
    } else {
      return (null);
    }
  } else {
    return (null);
  }
});

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
  return (modes[settings.value.audioMotionAnalyzer.mode]);
});
const selectedmaxFPSLabel = computed(() => {
  if (settings.value.audioMotionAnalyzer.maxFPS != 0) {
    return (settings.value.audioMotionAnalyzer.maxFPS);
  } else {
    return ('unlimited');
  }
});
const selectedReflexRatioLabel = computed(() => {
  if (settings.value.audioMotionAnalyzer.reflexRatio != 0) {
    return (settings.value.audioMotionAnalyzer.reflexRatio);

  } else {
    return ('no reflection');
  }
});

const analyzer = ref(null);

function onAnalyzerCanvasResize(reason, instance) {
  if (reason == 'fschange') {
    if (!instance.isFullscreen) {
      if (analyzer.value) {
        analyzer.value.destroy();
      }
      bus.emit('hideFullScreenVisualization');
    }
  }
};

const defaultAudioMotionAnalyzerSettings = {
  source: null,
  fsElement: null,
  connectSpeakers: false,
  gradient: 'classic',
  fullScreen: false,
  mode: 2,
  mirror: 0,
  height: 625,
  barSpace: 0.6,
  maxFPS: 60,
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
  onCanvasResize: null
};

const settings = ref(session.getFullScreenVisualizationSettings || { audioMotionAnalyzer: defaultAudioMotionAnalyzerSettings });

settings.value.anazly
function onSet(optionName, optionValue) {
  const option = {};
  option[optionName] = optionValue;
  analyzer.value.setOptions(option);
  session.saveFullScreenVisualizationSettings(settings.value);
}

function createAnalyzer() {
  if (!analyzer.value) {
    // custom saved gradients not found on init, set value after register custom gradients
    const savedGradient = settings.value.audioMotionAnalyzer.gradient || 'classic';
    settings.value.audioMotionAnalyzer.gradient = 'classic';
    settings.value.audioMotionAnalyzer.source = audioElement.value;
    settings.value.audioMotionAnalyzer.fsElement = document.getElementById('visualization-container');
    settings.value.audioMotionAnalyzer.onCanvasResize = onAnalyzerCanvasResize;
    analyzer.value = new AudioMotionAnalyzer(
      document.getElementById('analyzer-canvas-container'),
      settings.value.audioMotionAnalyzer
    );
    for (const [key, value] of Object.entries(staticGradients)) {
      analyzer.value.registerGradient(key, { bgColor: value.bgColor || '#111', colorStops: value.colorStops || [] });
    }
    settings.value.audioMotionAnalyzer.gradient = savedGradient;
    analyzer.value.setOptions({ gradient: savedGradient });
  }
  if (!analyzer.value.isOn) {
    analyzer.value.toggleAnalyzer();
    analyzer.value.toggleFullscreen();
  }
}


function formatSecondsAsTime(secs, format) {
  if (secs && Number.isInteger(secs) && secs > 0) {
    var hr = Math.floor(secs / 3600);
    var min = Math.floor((secs - (hr * 3600)) / 60);
    var sec = Math.floor(secs - (hr * 3600) - (min * 60));

    if (min < 10) {
      min = "0" + min;
    }
    if (sec < 10) {
      sec = "0" + sec;
    }
    return min + ':' + sec;
  } else {
    return '00:00';
  }
}

const emit = defineEmits(['hide', 'skipPrevious', 'play', 'skipNext']);

function onPlay() {
  spieldoseEvents.emit.currentPlaylist.play();
  emit('play');
}

function onSkipPrevious() {
  spieldoseEvents.emit.currentPlaylist.skipToPreviousTrack();
  emit('skipPrevious');
}

function skipToNextTrack() {
  spieldoseEvents.emit.currentPlaylist.nextTrack();
  emit('skipNext');
}

function onClose() {
  analyzer.value.toggleAnalyzer();
}

onMounted(() => {
  createAnalyzer();
});

</script>
