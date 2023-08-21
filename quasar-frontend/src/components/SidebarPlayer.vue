<template>
  <div>
    <SidebarPlayerAlbumCover></SidebarPlayerAlbumCover>
    <audio id="audio" class="is-hidden"></audio>
    <div id="analyzer-container" v-show="showAnalyzer" @click="onChangeaudioElementMotionAnalyzerMode"></div>
    <SidebarPlayerVolumeControl :disabled="disablePlayerControls" @volumeChange="onVolumeChange"></SidebarPlayerVolumeControl>
    <SidebarPlayerTrackInfo :currentTrack="currentPlaylist.getCurrentTrack"></SidebarPlayerTrackInfo>
    <SidebarPlayerMainControls :disabled="disablePlayerControls" :allowSkipPrevious="currentPlaylist.allowSkipPrevious" :allowPlay="true" :allowSkipNext="currentPlaylist.allowSkipNext" @skipPrevious="skipPrevious" @play="play" @skipNext="skipNext"></SidebarPlayerMainControls>
    <SidebarPlayerSeekControl :disabled="disablePlayerControls" :currentTrackTimeData="currentTrackTimeData" @seek="onSeek"></SidebarPlayerSeekControl>
    <SidebarPlayerTrackActions :disabled="disablePlayerControls" :downloadURL="currentTrackURL" @toggleAnalyzer="showAnalyzer = !showAnalyzer"></SidebarPlayerTrackActions>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";

import AudioMotionAnalyzer from 'audiomotion-analyzer';

import { default as SidebarPlayerAlbumCover } from "components/SidebarPlayerAlbumCover.vue";
import { default as SidebarPlayerVolumeControl } from "components/SidebarPlayerVolumeControl.vue";
import { default as SidebarPlayerTrackInfo } from "components/SidebarPlayerTrackInfo.vue";
import { default as SidebarPlayerMainControls } from "components/SidebarPlayerMainControls.vue";
import { default as SidebarPlayerSeekControl } from "components/SidebarPlayerSeekControl.vue";
import { default as SidebarPlayerTrackActions } from "components/SidebarPlayerTrackActions.vue";
import { useCurrentPlaylistStore } from 'stores/currentPlaylist'

const audioElement = ref(null);
const audioElementMotion = ref(null);

const showAnalyzer = ref(true);
const audioElementMotionMode = ref(4);

const currentPlaylist = useCurrentPlaylistStore();

const currentTrackURL = computed(() => {
  const currentTrack = currentPlaylist.getCurrentTrack;
  return (currentTrack ? "api/2/file/" + currentTrack.id : null);
});

const disablePlayerControls = computed(() => {
  return(false);
});

const currentTrackTimeData = ref({
  duration: 0,
  currentTime: 0,
  currentProgress: 0,
  position: 0,
});

watch(currentTrackURL, (newValue) => {
  audioElement.value.src = newValue;
  play();
});

function createAnalyzer() {
  if (!audioElementMotion.value) {
    audioElementMotion.value = new AudioMotionAnalyzer(
      document.getElementById('analyzer-container'),
      {
        source: audioElement.value,
        mode: audioElementMotionMode.value,
        height: 40,
        ledBars: false,
        showScaleX: false,
        showScaleY: false,
        stereo: false,
        splitGradient: false,
        start: false,
        bgAlpha: 1,
        overlay: true,
        showBgColor: true
      }
    );
    const options = {
      /*
      bgColor: '#011a35', // background color (optional) - defaults to '#111'
      dir: 'h',           // add this property to create a horizontal gradient (optional)
      colorStops: [       // list your gradient colors in this array (at least 2 entries are required)
          'red',                      // colors may be defined in any valid CSS format
          { pos: .6, color: '#ff0' }, // use an object to adjust the position (0 to 1) of a color
          'hsl( 120, 100%, 50% )'     // colors may be defined in any valid CSS format
      ]
      */
      bgColor: '#fff', // background color (optional) - defaults to '#111'
      dir: 'v',           // add this property to create a horizontal gradient (optional)
      colorStops: [       // list your gradient colors in this array (at least 2 entries are required)
        '#d30320', // colors may be defined in any valid CSS format
        //'#020024',
        '#e399a3'                      // colors may be defined in any valid CSS format

      ]
    }
    audioElementMotion.value.registerGradient('my-grad', options);
    audioElementMotion.value.gradient = 'my-grad';
  }
  if (!audioElementMotion.value.isOn) {
    audioElementMotion.value.toggleAnalyzer();
  }
}

onMounted(() => {

  console.log("mounted");
  audioElement.value = document.getElementById('audio');
  audioElement.value.volume = 1;

  audioElement.value.addEventListener('canplay', (event) => {
    console.log("Buffering audio end");
    console.debug('Audio can be played');
  });
  audioElement.value.addEventListener('play', (event) => {
    console.debug('Audio is playing1');
  });
  audioElement.value.addEventListener('playing', (event) => {
    console.debug('Audio is playing2');
  });
  audioElement.value.addEventListener('error', (event) => {
    console.debug('Audio loading error');
    console.log(event);
  });

  audioElement.value.addEventListener('timeupdate', (event) => {
    currentTrackTimeData.value.currentProgress = audioElement.value.currentTime / audioElement.value.duration;
    currentTrackTimeData.value.duration = Math.floor(audioElement.value.duration);
    currentTrackTimeData.value.currentTime = Math.floor(audioElement.value.currentTime);
    if (!isNaN(currentTrackTimeData.value.currentProgress)) {
      currentTrackTimeData.value.position = Number(currentTrackTimeData.value.currentProgress.toFixed(2));
    } else {
      currentTrackTimeData.value.position = 0;
    }
  });

  createAnalyzer();
});

function skipPrevious() {
  console.log("previosu");
  currentPlaylist.currentIndex--;
}

function skipNext() {
  console.log("next");
  currentPlaylist.currentIndex++;
}

function play() {
  audioElement.value.load();
  console.log("play");
  audioElement.value.play();
}

function onVolumeChange(volume) {
  audioElement.value.volume = volume;
}

function onSeek(position) {
  if (position >= 0 && position < 1) {
    audioElement.value.currentTime = audioElement.value.duration * position;
  }
}



</script>
