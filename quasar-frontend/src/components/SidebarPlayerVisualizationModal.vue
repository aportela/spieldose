<template>
  <q-dialog v-model="visible" @hide="onHide" @show="createAnalyzer" full-width full-height>
    <q-card class="my-card" style="width: 100%">
      <q-card-section>
        <div class="text-h6">Spieldose analyzer settings</div>
      </q-card-section>
      <q-card-section>
        <div class="row">
          <div class="col-2">
            <q-item-label header>Max FPS</q-item-label>
            <q-item dense>
              <q-item-section>
                <q-slider v-model="maxFPS" :min="0" :max="120" :step="30" label label-always color="grey"
                  @change="onSetMAXFPS" :label-value="selectedmaxFPSLabel" />
              </q-item-section>
            </q-item>
          </div>
          <div class="col-2">
            <q-item-label header>Gradient</q-item-label>
            <q-item dense>
              <q-item-section>
                <q-btn-toggle dense v-model="gradient" outline toggle-color="pink" spread :options="[
                  { label: 'classic', value: 'classic' },
                  { label: 'prism', value: 'prism' },
                  { label: 'rainbow', value: 'rainbow' }
                ]" @update:model-value="onSetGradient" />
              </q-item-section>
            </q-item>
          </div>
          <div class="col-2">
            <q-item-label header>Peaks</q-item-label>
            <q-item dense>
              <q-item-section>
                <q-btn-toggle dense v-model="showPeaks" outline toggle-color="pink" spread :options="[
                  { label: 'ON', value: true },
                  { label: 'OFF', value: false }
                ]" @update:model-value="onSetShowPeaks" :disable="lumiBars" />
              </q-item-section>
            </q-item>
          </div>
          <div class="col-2">
            <q-item-label header>Analyzer mode</q-item-label>
            <q-item dense>
              <q-item-section>
                <q-slider dense v-model="mode" :min="0" :max="10" :step="1" label :label-value="selectedModeLabel"
                  label-always switch-label-side color="grey" @change="onSetMode" />
              </q-item-section>
            </q-item>
          </div>
          <div class="col-2">
            <q-item-label header>Channel Layout</q-item-label>
            <q-item dense>
              <q-item-section>
                <q-btn-toggle dense v-model="channelLayout" outline toggle-color="pink" spread :options="[
                  { label: 'mono', value: 'single' },
                  { label: 'stereo', value: 'dual-vertical' }
                ]" @update:model-value="onSetChannelLayout" />
              </q-item-section>
            </q-item>
          </div>
          <div class="col-2">
            <q-item-label header>Screen mode</q-item-label>
            <q-item dense>
              <q-item-section>
                <q-btn-toggle dense v-model="fullScreen" outline toggle-color="pink" spread :options="[
                  { label: 'windowed', value: false },
                  { label: 'fullscreen', value: true }
                ]" @update:model-value="onSetFullScreen" />
              </q-item-section>
            </q-item>
          </div>

        </div>
        <q-item-label header>Horizontal mirror</q-item-label>
        <q-item dense>
          <q-item-section>
            <q-btn-toggle dense v-model="horizontalMirror" outline toggle-color="pink" spread :options="[
              { label: 'Left', value: -1 },
              { label: 'None', value: 0 },
              { label: 'Right', value: 1 }
            ]" @update:model-value="onSetHorizontalMirror" />
          </q-item-section>
        </q-item>

        <div v-if="mode > 0 && mode < 9">

          <q-item-label header>Octave bar settings</q-item-label>

          <q-item-label header>Bar space</q-item-label>
          <q-item dense>
            <q-item-section>
              <q-slider dense v-model="barSpace" :min="0" :max="1" :step="0.1" label label-always color="grey"
                @change="onSetBarSpace" />
            </q-item-section>
          </q-item>

          <div class="row">
            <div class="col-2">
              <q-item-label header>Led bars</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-btn-toggle dense v-model="ledBars" outline toggle-color="pink" spread :options="[
                    { label: 'ON', value: true },
                    { label: 'OFF', value: false }
                  ]" @update:model-value="onSetLedBars" />
                </q-item-section>
              </q-item>
            </div>
            <div class="col-2">
              <q-item-label header>True leds</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-btn-toggle dense v-model="trueLeds" outline toggle-color="pink" spread :options="[
                    { label: 'ON', value: true },
                    { label: 'OFF', value: false }
                  ]" @update:model-value="onSetTrueLeds" />
                </q-item-section>
              </q-item>
            </div>
            <div class="col-2">
              <q-item-label header>Lumi bars</q-item-label>
              <q-item dense>
                <q-item-section>
                  <q-btn-toggle dense v-model="lumiBars" outline toggle-color="pink" spread :options="[
                    { label: 'ON', value: true },
                    { label: 'OFF', value: false }
                  ]" @update:model-value="onSetLumiBars" />
                </q-item-section>
              </q-item>
            </div>

          </div>
        </div>

        <div class="row">
          <div class="col-3">
            <q-item-label header>Reflex</q-item-label>
            <q-item dense>
              <q-item-section>
                <q-slider v-model="reflexRatio" :min="0" :max="0.9" :step="0.1" label label-always color="grey"
                  @change="onSetReflexRatio" :label-value="selectedReflexRatioLabel" />
              </q-item-section>
            </q-item>
          </div>
          <div class="col-3">
            <q-item-label header>Reflex alpha</q-item-label>
            <q-item dense>
              <q-item-section>
                <q-slider v-model="reflexAlpha" :min="0" :max="1" :step="0.1" label label-always color="grey"
                  @change="onSetReflexAlpha" />
              </q-item-section>
            </q-item>
          </div>
          <div class="col-3">
            <q-item-label header>Reflex bright</q-item-label>
            <q-item dense>
              <q-item-section>
                <q-slider v-model="reflexBright" :min="0" :max="1" :step="0.1" label label-always color="grey"
                  @change="onSetReflexBright" />
              </q-item-section>
            </q-item>
          </div>
        </div>

        <!-- TODO separated component -->
        <div id="analyzer-container-overlay"
          style="width: 100%; background-color: #000; background-image: url('images/overlay.jpg'); background-repeat: no-repeat; background-position: 50%; background-size: auto 150%;">
          <div id="analyzer-container-settings" style="width: 100%; height: 400px"></div>
          <div class="row">
            <div class="col-6">
              <div style="margin: 64px;">
                <q-img img-class="fullscreen_album_cover shadow-18" v-if="coverImage" :src="coverImage" width="400px"
                  height="400px" spinner-color="pink" />
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
                <q-btn round dense size="30px" color="dark" style="opacity: 0.8" :disable="disabled || !allowSkipPrevious"
                  @click="onSkipPrevious"><q-icon name="skip_previous" title="Skip to previous track"></q-icon></q-btn>
                <q-btn round dense class="q-mx-lg" size="60px" color="dark" style="opacity: 0.8"
                  :disable="disabled || !allowPlay" @click="onPlay"><q-icon :name="isPlaying ? 'pause' : 'play_arrow'"
                    :class="{ 'text-pink-6': isPlaying }" title="Play/Pause/Resume track"></q-icon></q-btn>
                <q-btn round dense size="30px" color="dark" style="opacity: 0.8" :disable="disabled || !allowSkipNext"
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
                    <q-item-label overline>Spieldose</q-item-label>
                    <q-item-label>Playlist</q-item-label>
                    <q-item-label caption>Current playlist</q-item-label>
                  </q-item-section>
                  <q-item-section side top>
                    <q-item-label caption>5 min ago</q-item-label>
                  </q-item-section>
                </q-item>
                <q-separator spaced />
                <q-item-label header>Tracks</q-item-label>
                <q-scroll-area style="height: 400px; max-width: 400px;" dark visible>
                  <q-item clickable v-for="element in currentPlaylist.getElements" :key="element.id">
                    <q-item-section avatar>
                      <q-avatar>
                        <q-img :src="element.track.covers.small" v-if="element.track.covers.small">
                          <template v-slot:error>
                            <q-icon name="album"></q-icon>
                          </template>
                        </q-img>
                        <q-icon name="album" v-else></q-icon>
                      </q-avatar>
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>{{ element.track.title }}</q-item-label>
                      <q-item-label caption>{{ element.track.artist.name }}</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-scroll-area>
              </q-list>
            </div>
          </div>
        </div>
      </q-card-section>
      <q-separator />
    </q-card>
  </q-dialog>
</template>

<style>
.fullscreen_album_cover {
  position: relative;
  bottom: 32px;
  left: 32px;

}
</style>
<script setup>
import { ref, computed } from "vue";
import AudioMotionAnalyzer from 'audiomotion-analyzer';
import { usePlayer } from 'stores/player';
import { useCurrentPlaylistStore } from 'stores/currentPlaylist';

const currentPlaylist = useCurrentPlaylistStore();
const props = defineProps({
  coverImage: String,
  currentElement: Object,
  disabled: Boolean,
  allowSkipPrevious: Boolean,
  allowPlay: Boolean,
  allowSkipNext: Boolean,
  isPlaying: Boolean,
  currentTrackIndex: Number,
  totalTracks: Number,
  currentElementTimeData: Object
});


// TODO: not working
const showAlbumCover = computed(() => {
  if (analyzer.value) {
    return (analyzer.value.isFullscreen);
  } else {
    return (false);
  }
});

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
  emit('play');
}

function onSkipPrevious() {
  emit('skipPrevious');
}

function onSkipNext() {
  emit('skipNext');
}

const visible = ref(true);

function onHide() {
  if (analyzer.value) {
    analyzer.value.destroy();
  }
  visible.value = false;
  emit('hide');
}

const gradient = ref('rainbow');

function onSetGradient(v) {
  analyzer.value.setOptions({ gradient: v });
}

const fullScreen = ref(false);

function onSetFullScreen(v) {
  if (v) {
    analyzer.value.toggleFullscreen();
  }
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
  return (modes[mode.value]);
});

const mode = ref(3);

function onSetMode(v) {
  analyzer.value.setOptions({ mode: v != 9 ? v : 10 });
}

const horizontalMirror = ref(0);

function onSetHorizontalMirror(v) {
  analyzer.value.setOptions({ mirror: v });
}

const barSpace = ref(0.2);

function onSetBarSpace(v) {
  analyzer.value.setOptions({ barSpace: v });
}

const maxFPS = ref(60);

function onSetMAXFPS(v) {
  analyzer.value.setOptions({ maxFPS: v });
}

const selectedmaxFPSLabel = computed(() => {
  if (maxFPS.value != 0) {
    return (maxFPS.value);
  } else {
    return ('unlimited');
  }
});

const ledBars = ref(true);

function onSetLedBars(v) {
  analyzer.value.setOptions({ ledBars: v });
}

const trueLeds = ref(false);

function onSetTrueLeds(v) {
  analyzer.value.setOptions({ trueLeds: v });
}

const lumiBars = ref(false);

function onSetLumiBars(v) {
  analyzer.value.setOptions({ lumiBars: v });
}

const showPeaks = ref(true);

function onSetShowPeaks(v) {
  analyzer.value.setOptions({ showPeaks: v });
}

const channelLayout = ref('single');

function onSetChannelLayout(v) {
  analyzer.value.setOptions({ channelLayout: v });
}

const reflexRatio = ref(0.4);

const selectedReflexRatioLabel = computed(() => {
  if (reflexRatio.value != 0) {
    return (reflexRatio.value);

  } else {
    return ('no relection');
  }
});

function onSetReflexRatio(v) {
  analyzer.value.setOptions({ reflexRatio: v });
}

const reflexAlpha = ref(0.5);

function onSetReflexAlpha(v) {
  analyzer.value.setOptions({ reflexAlpha: v });
}

const reflexBright = ref(1);

function onSetReflexBright(v) {
  analyzer.value.setOptions({ reflexBright: v });
}

const player = usePlayer();
const audioElement = ref(player.getElement);
const analyzer = ref(null);

function createAnalyzer() {
  if (!analyzer.value) {
    analyzer.value = new AudioMotionAnalyzer(
      document.getElementById('analyzer-container-settings'),
      {
        source: audioElement.value,
        fsElement: document.getElementById('analyzer-container-overlay'),
        overlay: true,
        showBgColor: true,
        bgAlpha: 0.5,
        mode: mode.value,
        //height: 600,
        ledBars: ledBars.value,
        trueLeds: trueLeds.value,
        peakLine: true,
        showScaleX: false,
        showScaleY: false,
        channelLayout: channelLayout.value,
        splitGradient: false,
        start: false,
        maxFPS: maxFPS.value,
        reflexRatio: reflexRatio.value,
        reflexAlpha: reflexAlpha.value,
        reflexBright: reflexBright.value,
        reflexFit: true,
        gradient: gradient.value,
        onCanvasResize: (reason, instance) => {
          console.log();
          if (reason == 'fschange') {
            fullScreen.value = instance.isFullscreen;
            if (!instance.isFullscreen) {
              onHide();
            }
          }
        }
      }
    );
  }
  if (!analyzer.value.isOn) {
    analyzer.value.toggleAnalyzer();
  }
}

</script>
