<template>
  <div v-if="currentElement">
    <SidebarPlayerAlbumCover :coverImage="coverImage" :smallVinylImage="smallVinylImage"
      :rotateVinyl="spieldosePlayer.isPlaying()"></SidebarPlayerAlbumCover>
    <audio id="audio" class="is-hidden"></audio>
    <SidebarPlayerSpectrumAnalyzer v-show="showAnalyzer" :active="showAnalyzer"></SidebarPlayerSpectrumAnalyzer>
    <SidebarPlayerVolumeControl :disabled="disablePlayerControls" :defaultValue="defaultVolume"
      @volumeChange="onVolumeChange">
    </SidebarPlayerVolumeControl>
    <SidebarPlayerTrackInfo :currentElement="currentElement"></SidebarPlayerTrackInfo>
    <SidebarPlayerMainControls :disabled="disablePlayerControls" :allowSkipPrevious="spieldosePlayer.allowSkipPrevious()"
      :allowPlay="true" :allowSkipNext="spieldosePlayer.allowSkipNext()" :isPlaying="spieldosePlayer.isPlaying()"
      @skipPrevious="skipPrevious" @play="play" @skipNext="skipNext"></SidebarPlayerMainControls>
    <SidebarPlayerSeekControl :disabled="disablePlayerControls || !isCurrentElementTrack"
      :currentElementTimeData="currentElementTimeData" @seek="onSeek"></SidebarPlayerSeekControl>
    <SidebarPlayerTrackActions :disabled="disablePlayerControls" :id="currentElementId"
      :downloadURL="currentElement.track.url" :isTrackFavorited="currentElementFavorited"
      :visibleAnalyzer="showAnalyzer" :shuffle="spieldosePlayer.getShuffle()" :repeatMode="spieldosePlayer.getRepeatMode()"
      @toggleAnalyzer="showAnalyzer = !showAnalyzer" @toggleShuffle="onToggleShuffle"
      @toggleRepeatMode="onToggleRepeatMode" @toggleTrackDetailsModal="detailsModal = true">
    </SidebarPlayerTrackActions>
    <SidebarPlayerTrackDetailsModal v-if="detailsModal" :coverImage="coverImage" :trackId="currentElementId"
      @hide="detailsModal = false">
    </SidebarPlayerTrackDetailsModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, inject } from "vue";
import { useQuasar } from "quasar";
import { useI18n } from 'vue-i18n';

import { default as SidebarPlayerAlbumCover } from "components/SidebarPlayerAlbumCover.vue";
import { default as SidebarPlayerSpectrumAnalyzer } from "components/SidebarPlayerSpectrumAnalyzer.vue";
import { default as SidebarPlayerVolumeControl } from "components/SidebarPlayerVolumeControl.vue";
import { default as SidebarPlayerTrackInfo } from "components/SidebarPlayerTrackInfo.vue";
import { default as SidebarPlayerMainControls } from "components/SidebarPlayerMainControls.vue";
import { default as SidebarPlayerSeekControl } from "components/SidebarPlayerSeekControl.vue";
import { default as SidebarPlayerTrackActions } from "components/SidebarPlayerTrackActions.vue";
import { default as SidebarPlayerTrackDetailsModal } from "components/SidebarPlayerTrackDetailsModal.vue";
import { useCurrentPlaylistStore } from 'stores/currentPlaylist'

import { spieldoseEvents } from "boot/events";
import { playListActions } from "src/boot/spieldose";

const $q = useQuasar();
const { t } = useI18n();


const spieldosePlayer = inject('spieldosePlayer');

const defaultVolume = spieldosePlayer.getVolume || 1;

const currentElement = spieldosePlayer.getCurrentPlaylistElement();
console.log(currentElement);

const audioElement = ref(null);

const detailsModal = ref(false);

const showAnalyzer = ref(true);

const currentPlaylist = useCurrentPlaylistStore();

const isCurrentElementTrack = computed(() => {
  const currentElement = spieldosePlayer.getCurrentPlaylistElement();
  return (currentElement.track != null);
});

// TODO: use currentElement globally

const currentElementId = computed(() => {
  if (currentElement && currentElement.track) {
    return (currentElement.track.id || null);
  } else if (currentElement && currentElement.radioStation) {
    return (currentElement.radioStation.id || null);
  } else {
    return (null);
  }
});

const currentElementFavorited = computed(() => {
  if (currentElement && currentElement.track) {
    return (currentElement.track.favorited || null);
  } else {
    return (null);
  }
});

const coverImage = computed(() => {
  if (currentElement) {
    if (currentElement.track && currentElement.track.covers) {
      return (currentElement.track.covers.normal || null);
    } else if (currentElement.radioStation && currentElement.radioStation.images) {
      return (currentElement.radioStation.images.normal || null);
    } else {
      return (null);
    }
  } else {
    return (null);
  }
});

const smallVinylImage = computed(() => {
  if (currentElement) {
    if (currentElement.track && currentElement.track.covers) {
      return (currentElement.track.covers.small || null);
    } else if (currentElement.radioStation && currentElement.radioStation.images) {
      return (currentElement.radioStation.images.small || null);
    } else {
      return (null);
    }
  } else {
    return (null);
  }
});

const disablePlayerControls = computed(() => {
  return (currentElementId.value == null);
});

const currentElementTimeData = ref({
  duration: 0,
  currentTime: 0,
  currentProgress: 0,
  position: 0,
});

onMounted(() => {
  audioElement.value = spieldosePlayer.getAudioInstance();
  spieldosePlayer.actions.setVolume(defaultVolume);

  /*
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
  */
  audioElement.value.addEventListener('ended', (event) => {
    console.debug('Audio is ended');
    if (isCurrentElementTrack.value) {
      spieldoseEvents.increasePlayCount(currentElementId.value);
    }
    if (spieldosePlayer.getRepeatMode() == 'playlist') {
      if (spieldosePlayer.allowSkipNext()) {
        skipNext();
      } else {
        //currentPlaylist.saveCurrentTrackIndex(0);
        spieldosePlayer.actions.stop();
        spieldosePlayer.actions.play();
      }
    } else if (spieldosePlayer.getRepeatMode() == 'track') {
      spieldosePlayer.actions.stop();
      spieldosePlayer.actions.play();
    } else {
      if (spieldosePlayer.allowSkipNext()) {
        skipNext();
      } else {
        spieldosePlayer.actions.stop();
      }
    }
  });
  audioElement.value.addEventListener('error', (event) => {
    console.debug('Audio loading error');
    console.log(event);
  });

  audioElement.value.addEventListener('timeupdate', (event) => {
    currentElementTimeData.value.currentProgress = audioElement.value.currentTime / audioElement.value.duration;
    currentElementTimeData.value.duration = Math.floor(audioElement.value.duration);
    currentElementTimeData.value.currentTime = Math.floor(audioElement.value.currentTime);
    if (!isNaN(currentElementTimeData.value.currentProgress)) {
      currentElementTimeData.value.position = Number(currentElementTimeData.value.currentProgress.toFixed(2));
    } else {
      currentElementTimeData.value.position = 0;
    }
  });
});

function skipPrevious() {
  spieldosePlayer.interact();
  spieldosePlayer.actions.skipPrevious();
}

function play(ignoreStatus) {
  spieldosePlayer.interact();
  spieldosePlayer.actions.play(ignoreStatus);
}

function onToggleRepeatMode() {
  spieldosePlayer.actions.toggleRepeatMode();
}

function onToggleShuffle() {
  spieldosePlayer.actions.toggleShuffeMode();
}

function skipNext() {
  spieldosePlayer.interact();
  spieldosePlayer.actions.skipNext();
}

function onVolumeChange(volume) {
  spieldosePlayer.actions.setVolume(volume);
  //session.saveVolume(volume);
}

function onSeek(position) {
  if (position >= 0 && position < 1) {
    spieldosePlayer.actions.setCurrentTime(spieldosePlayer.getDuration() * position);
  }
}

const defaultSettings = {
  audioMotionAnalyzer: {
    visible: true,
    mode: 7
  }
};

const settings =  defaultSettings;
//const settings = session.getSidebarPlayerSettings || defaultSettings;
//console.log(settings);
//session.saveSidebarPlayerSettings();

</script>
