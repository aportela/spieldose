<template>
  <div>
    <SidebarPlayerAlbumCover :coverImage="coverImage" :smallVinylImage="smallVinylImage"
      :rotateVinyl="playerStatus.isPlaying"></SidebarPlayerAlbumCover>
    <audio id="audio" class="is-hidden"></audio>
    <SidebarPlayerSpectrumAnalyzer v-show="showAnalyzer" :active="showAnalyzer"></SidebarPlayerSpectrumAnalyzer>
    <SidebarPlayerVolumeControl :disabled="disablePlayerControls" :defaultValue="defaultVolume"
      @volumeChange="onVolumeChange">
    </SidebarPlayerVolumeControl>
    <SidebarPlayerTrackInfo :currentElement="currentPlaylist.getCurrentElement"></SidebarPlayerTrackInfo>
    <SidebarPlayerMainControls :disabled="disablePlayerControls" :allowSkipPrevious="currentPlaylist.allowSkipPrevious"
      :allowPlay="true" :allowSkipNext="currentPlaylist.allowSkipNext" :isPlaying="playerStatus.isPlaying"
      @skipPrevious="skipPrevious" @play="play" @skipNext="skipNext"></SidebarPlayerMainControls>
    <SidebarPlayerSeekControl :disabled="disablePlayerControls || !isCurrentElementTrack"
      :currentElementTimeData="currentElementTimeData" @seek="onSeek"></SidebarPlayerSeekControl>
    <SidebarPlayerTrackActions :disabled="disablePlayerControls" :id="currentElementId"
      :downloadURL="currentPlaylist.getCurrentElementURL" :isTrackFavorited="currentElementFavorited"
      :visibleAnalyzer="showAnalyzer" :shuffle="player.getShuffle" :repeatMode="player.getRepeatMode"
      @toggleAnalyzer="showAnalyzer = !showAnalyzer" @toggleShuffle="onToggleShuffle"
      @toggleRepeatMode="onToggleRepeatMode" @toggleTrackDetailsModal="detailsModal = true">
    </SidebarPlayerTrackActions>
    <SidebarPlayerTrackDetailsModal v-if="detailsModal" :coverImage="coverImage" :trackId="currentElementId"
      @hide="detailsModal = false">
    </SidebarPlayerTrackDetailsModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";

import { usePlayer } from 'stores/player';

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
import { useSessionStore } from 'stores/session'
import { useCurrentPlaylistStore } from 'stores/currentPlaylist'
import { usePlayerStatusStore } from 'stores/playerStatus'
import { api } from "src/boot/axios";

const $q = useQuasar();
const { t } = useI18n();

const session = useSessionStore();
if (!session.isLoaded) {
  session.load();
}

const loading = ref(false);

const defaultVolume = session.getVolume || 1;

const player = usePlayer();

const audioElement = ref(null);

const detailsModal = ref(false);

const showAnalyzer = ref(true);

const currentPlaylist = useCurrentPlaylistStore();

const playerStatus = usePlayerStatusStore();

const isCurrentElementTrack = computed(() => {
  const currentElement = currentPlaylist.getCurrentElement;
  return (currentElement.track != null);
});

// TODO: use currentElement globally

const currentElementId = computed(() => {
  const currentElement = currentPlaylist.getCurrentElement;
  if (currentElement && currentElement.track) {
    return (currentElement.track.id || null);
  } else if (currentElement && currentElement.radioStation) {
    return (currentElement.radioStation.id || null);
  } else {
    return (null);
  }
});

const currentElementFavorited = computed(() => {
  const currentElement = currentPlaylist.getCurrentElement;
  if (currentElement && currentElement.track) {
    return (currentElement.track.favorited || null);
  } else {
    return (null);
  }
});

const coverImage = computed(() => {
  const currentElement = currentPlaylist.getCurrentElement;
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
  const currentElement = currentPlaylist.getCurrentElement;
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
  audioElement.value = player.getElement;
  player.setVolume(defaultVolume);

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
      increasePlayCount(currentElementId.value);
    }
    if (player.getRepeatMode == 'playlist') {
      if (currentPlaylist.allowSkipNext) {
        skipNext();
      } else {
        currentPlaylist.saveCurrentTrackIndex(0);
        player.stop();
        player.play();
      }
    } else if (player.getRepeatMode == 'track') {
      player.stop();
      player.play();
    } else {
      if (currentPlaylist.allowSkipNext) {
        skipNext();
      } else {
        playerStatus.setStatusStopped();
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

function increasePlayCount(trackId) {
  // WARNING: TODO CALL MULTIPLE TIMES ?
  api.track.increasePlayCount(trackId).then((success) => {
  })
    .catch((error) => {
      loading.value = false;
      switch (error.response.status) {
        default:
          $q.notify({
            type: "negative",
            message: t("API Error: fatal error"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
    });
}

function skipPrevious() {
  player.interact();
  currentPlaylist.skipPrevious();
}

function play(ignoreStatus) {
  player.interact();
  player.play(ignoreStatus);
}

function onToggleRepeatMode() {
  switch (player.getRepeatMode) {
    case 'track':
      player.setRepeatMode('playlist');
      break;
    case 'playlist':
      player.setRepeatMode(null);
      break;
    default:
      player.setRepeatMode('track');
      break;
  }
}

function onToggleShuffle() {
  player.toggleShuffle();
}

function skipNext() {
  player.interact();
  currentPlaylist.skipNext();
}

function onVolumeChange(volume) {
  player.setVolume(volume);
  session.saveVolume(volume);
}

function onSeek(position) {
  if (position >= 0 && position < 1) {
    player.setCurrentTime(player.getDuration * position);
  }
}

const defaultSettings = {
  audioMotionAnalyzer: {
    visible: true,
    mode: 7
  }
};

const settings = session.getSidebarPlayerSettings || defaultSettings;
console.log(settings);
session.saveSidebarPlayerSettings();

</script>
