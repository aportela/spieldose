<template>
  <div>
    <SidebarPlayerAlbumCover :normalImage="spieldoseStore.getCurrentPlaylistElementNormalImage"
      :smallImage="spieldoseStore.getCurrentPlaylistElementSmallImage"
      :animation="spieldoseStore.hasSidebarTopArtAnimationMode" :animated="spieldoseStore.isPlaying"
      @change="spieldoseStore.toggleSidebarTopArtAnimationMode()"></SidebarPlayerAlbumCover>
    <SidebarPlayerSpectrumAnalyzer v-show="spieldoseStore.isSidebarAudioMotionAnalyzerVisible"
      :create="spieldoseStore.hasPreviousUserInteractions && (spieldoseStore.hasCurrentPlaylistElements || spieldoseStore.hasCurrentPlaylistARadioStation)"
      :active="spieldoseStore.isSidebarAudioMotionAnalyzerVisible"
      :mode="spieldoseStore.getSidebarAudioMotionAnalyzerMode"
      @change="(data) => spieldoseStore.setSidebarAudioMotionAnalyzerMode(data.mode)"></SidebarPlayerSpectrumAnalyzer>
    <SidebarPlayerVolumeControl :isMuted="spieldoseStore.isMuted" :defaultValue="spieldoseStore.getVolume"
      @volumeChange="(volume) => spieldoseStore.setVolume(volume)" @toggleMute="spieldoseStore.toggleMute()">
    </SidebarPlayerVolumeControl>
    <SidebarPlayerTrackInfo :track="currentElement.track" :radioStation="currentElement.radioStation">
    </SidebarPlayerTrackInfo>
    <SidebarPlayerMainControls :disabled="false" :allowSkipPrevious="spieldoseStore.allowSkipPrevious"
      :allowPlay="spieldoseStore.hasCurrentPlaylistElements" :allowSkipNext="spieldoseStore.allowSkipNext"
      :playerStatus="spieldoseStore.getPlayerStatus" @skipPrevious="skipToPrevious()" @play="play()"
      @skipNext="skipToNext()"></SidebarPlayerMainControls>
    <SidebarPlayerSeekControl :disabled="disablePlayerControls || !isCurrentElementTrack"
      :currentElementTimeData="currentElementTimeData" @seek="onSeek"></SidebarPlayerSeekControl>
    <SidebarPlayerTrackActions :disabled="disablePlayerControls" :id="currentElementId"
      :downloadURL="spieldoseStore.isCurrentPlaylistElementATrack ? (spieldoseStore.getCurrentPlaylistElementURL || '#') : null"
      :trackFavoritedTimestamp="isCurrentElementTrack ? currentElement.track.favorited : null"
      :visibleAnalyzer="spieldoseStore.isSidebarAudioMotionAnalyzerVisible" :shuffle="spieldoseStore.getShuffle"
      :repeatMode="spieldoseStore.getRepeatMode" @toggleAnalyzer="spieldoseStore.toggleSidebarAudioMotionAnalyzer()"
      @toggleShuffle="spieldoseStore.toggleShuffeMode()" @toggleRepeatMode="spieldoseStore.toggleRepeatMode()"
      @toggleFavorite="onToggleFavorite" @toggleTrackDetailsModal="detailsModal = true">
    </SidebarPlayerTrackActions>
    <SidebarPlayerTrackDetailsModal v-if="detailsModal" :coverImage="coverImage" :trackId="currentElementId"
      @hide="detailsModal = false">
    </SidebarPlayerTrackDetailsModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from "vue";
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

import { spieldoseEventNames } from "boot/events";

import { useSpieldoseStore } from "stores/spieldose";
import { trackActions, currentPlayListActions } from "../boot/spieldose";

const $q = useQuasar();
const { t } = useI18n();

const spieldoseStore = useSpieldoseStore();

const currentElement = computed(() => { return (spieldoseStore.getCurrentPlaylistElement); });

const detailsModal = ref(false);

// TODO: use store getter
const isCurrentElementTrack = computed(() => {
  return (spieldoseStore.isCurrentPlaylistElementATrack);
});

// TODO: use currentElement globally

const currentElementId = computed(() => {
  if (currentElement.value && currentElement.value.track) {
    return (currentElement.value.track.id || null);
  } else if (currentElement.value && currentElement.value.radioStation) {
    return (currentElement.value.radioStation.id || null);
  } else {
    return (null);
  }
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

const smallVinylImage = computed(() => {
  if (currentElement.value) {
    if (currentElement.value.track && currentElement.value.track.covers) {
      return (currentElement.value.track.covers.small || null);
    } else if (currentElement.value.radioStation && currentElement.value.radioStation.images) {
      return (currentElement.value.radioStation.images.small || null);
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
  const audioElement = ref(spieldoseStore.getAudioInstance);

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
    if (isCurrentElementTrack.value) {
      // TODO, launch error
      trackActions.increasePlayCount(currentElementId.value).then((success) => { }).catch((error) => {
        switch (error.response.status) {
          default:
            // TODO: custom message
            $q.notify({
              type: "negative",
              message: t("API Error: error increasing track play count"),
              caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
            });
            break;
        }
      });
    }
    if (spieldoseStore.getRepeatMode == 'playlist') {
      if (spieldoseStore.allowSkipNext) {
      } else {
        spieldoseStore.stop();
        currentPlayListActions.skipToElementIndex(0).then((success) => { }).catch((error) => {
        // TODO
      });
        // TODO: not working, skip is fine, but playing has a bug
        //spieldoseStore.play();
      }
    } else if (spieldoseStore.getRepeatMode == 'track') {
      spieldoseStore.stop();
      spieldoseStore.play();
    } else {
      if (spieldoseStore.allowSkipNext) {
        currentPlayListActions.skipToNextElement().then((success) => { }).catch((error) => {
        // TODO
      });
      } else {
        spieldoseStore.stop();
      }
    }
  });
  audioElement.value.addEventListener('error', (event) => {
    console.debug('Audio loading error');
    console.log(event);
  });

  audioElement.value.addEventListener('timeupdate', (event) => {
    if (isCurrentElementTrack.value) {
      currentElementTimeData.value.currentProgress = audioElement.value.currentTime / audioElement.value.duration;
      currentElementTimeData.value.duration = Math.floor(audioElement.value.duration);
      currentElementTimeData.value.currentTime = Math.floor(audioElement.value.currentTime);
      if (!isNaN(currentElementTimeData.value.currentProgress)) {
        currentElementTimeData.value.position = Number(currentElementTimeData.value.currentProgress.toFixed(2));
      } else {
        currentElementTimeData.value.position = 0;
      }
    } else {
      // TODO: remove listener if no track (radiostations)
      currentElementTimeData.value.currentProgress = 0;
      currentElementTimeData.value.duration = 0;
      currentElementTimeData.value.currentTime = 0;
      currentElementTimeData.value.position = 0;
    }
  });
});


// TODO: update currentTime to 0  clear playlist (bug?)

function play(ignoreStatus) {
  spieldoseStore.interact();
  spieldoseStore.play(ignoreStatus);
}

function skipToPrevious() {
  spieldoseStore.interact();
  if (spieldoseStore.allowSkipPrevious) {
    currentPlayListActions.skipToPreviousElement().then((success) => { }).catch((error) => {
      // TODO
    });
  }
}

function skipToNext() {
  spieldoseStore.interact();
  if (spieldoseStore.allowSkipNext) {
    currentPlayListActions.skipToNextElement().then((success) => { }).catch((error) => {
      // TODO
    });
  }
}

function onSeek(position) {
  if (position >= 0 && position < 1) {
    spieldoseStore.setCurrentTime(spieldoseStore.getDuration * position);
  }
}

const defaultSettings = {
  audioMotionAnalyzer: {
    visible: true,
    mode: 7
  }
};

function onToggleFavorite() {
  const funct = !currentElement.value.track.favorited ? trackActions.setFavorite : trackActions.unSetFavorite;
  funct(currentElement.value.track.id).then((success) => {
    spieldoseStore.toggleFavoriteOnCurrentTrack(success.data.favorited);
  })
    .catch((error) => {
      switch (error.response.status) {
        default:
          // TODO: custom message
          $q.notify({
            type: "negative",
            message: t("API Error: error toggling favorite flag"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
    });
}
</script>
