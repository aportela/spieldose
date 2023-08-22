<template>
  <div>
    <SidebarPlayerAlbumCover :coverImage="coverImage" :smallVinylImage="smallVinylImage"
      :rotateVinyl="playerStatus.isPlaying"></SidebarPlayerAlbumCover>
    <audio id="audio" class="is-hidden"></audio>
    <SidebarPlayerSpectrumAnalyzer v-show="showAnalyzer"></SidebarPlayerSpectrumAnalyzer>
    <SidebarPlayerVolumeControl :disabled="disablePlayerControls" :defaultValue="defaultVolume"
      @volumeChange="onVolumeChange">
    </SidebarPlayerVolumeControl>
    <SidebarPlayerTrackInfo :currentTrack="currentPlaylist.getCurrentTrack"></SidebarPlayerTrackInfo>
    <SidebarPlayerMainControls :disabled="disablePlayerControls" :allowSkipPrevious="currentPlaylist.allowSkipPrevious"
      :allowPlay="true" :allowSkipNext="currentPlaylist.allowSkipNext" :isPlaying="playerStatus.isPlaying"
      @skipPrevious="skipPrevious" @play="play" @skipNext="skipNext"></SidebarPlayerMainControls>
    <SidebarPlayerSeekControl :disabled="disablePlayerControls" :currentTrackTimeData="currentTrackTimeData"
      @seek="onSeek"></SidebarPlayerSeekControl>
    <SidebarPlayerTrackActions :disabled="disablePlayerControls" :downloadURL="currentTrackURL"
      @toggleAnalyzer="showAnalyzer = !showAnalyzer"></SidebarPlayerTrackActions>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";



import { usePlayer } from 'stores/player';

import { default as SidebarPlayerAlbumCover } from "components/SidebarPlayerAlbumCover.vue";
import { default as SidebarPlayerSpectrumAnalyzer } from "components/SidebarPlayerSpectrumAnalyzer.vue";
import { default as SidebarPlayerVolumeControl } from "components/SidebarPlayerVolumeControl.vue";
import { default as SidebarPlayerTrackInfo } from "components/SidebarPlayerTrackInfo.vue";
import { default as SidebarPlayerMainControls } from "components/SidebarPlayerMainControls.vue";
import { default as SidebarPlayerSeekControl } from "components/SidebarPlayerSeekControl.vue";
import { default as SidebarPlayerTrackActions } from "components/SidebarPlayerTrackActions.vue";
import { useCurrentPlaylistStore } from 'stores/currentPlaylist'
import { usePlayerStatusStore } from 'stores/playerStatus'

const defaultVolume = 1;
const player = usePlayer();

const audioElement = ref(null);


const showAnalyzer = ref(true);

const currentPlaylist = useCurrentPlaylistStore();

const playerStatus = usePlayerStatusStore();

const currentTrackURL = computed(() => {
  const currentTrack = currentPlaylist.getCurrentTrack;
  return (currentTrack ? "api/2/file/" + currentTrack.id : null);
});

const currentTrackId = computed(() => {
  const currentTrack = currentPlaylist.getCurrentTrack;
  return (currentTrack ? currentTrack.id : null);
});

const coverImage = computed(() => {
  const currentTrack = currentPlaylist.getCurrentTrack;
  return (currentTrack ? "/api/2/track/thumbnail/normal/" + currentTrack.id : null);
});

const smallVinylImage = computed(() => {
  const currentTrack = currentPlaylist.getCurrentTrack;
  return (currentTrack ? "/api/2/track/thumbnail/small/" + currentTrack.id : null);
});
const disablePlayerControls = computed(() => {
  return (false);
});

const musicBrainzAlbumId = computed(() => {
  const currentTrack = currentPlaylist.getCurrentTrack;
  return (currentTrack ? currentTrack.musicBrainzAlbumId : null);
});

const currentTrackTimeData = ref({
  duration: 0,
  currentTime: 0,
  currentProgress: 0,
  position: 0,
});

watch(currentTrackURL, (newValue) => {
  if (audioElement.value) {
    audioElement.value.src = newValue;
    play(true);
  }
});


onMounted(() => {
  audioElement.value = player.getElement;
  player.setVolume(1);

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
  audioElement.value.addEventListener('ended', (event) => {
    console.debug('Audio is ended');
    if (currentPlaylist.allowSkipNext) {
      skipNext();
    } else {
      playerStatus.setStatusStopped();
    }
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

});

function skipPrevious() {
  console.log("previosu");
  currentPlaylist.currentIndex--;
}

function skipNext() {
  console.log("next");
  currentPlaylist.currentIndex++;
}

function play(ignoreStatus) {
  if (ignoreStatus) {
    audioElement.value.load();
    console.log("play");
    audioElement.value.play();
    playerStatus.setStatusPlaying();
  } else {
    if (playerStatus.isPlaying) {
      console.log("pause");
      audioElement.value.pause();
      playerStatus.setStatusPaused();
    } else if (playerStatus.isPaused) {
      console.log("resume");
      audioElement.value.play();
      playerStatus.setStatusPlaying();
    } else {
      audioElement.value.load();
      console.log("play");
      audioElement.value.play();
      playerStatus.setStatusPlaying();
    }
  }
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
