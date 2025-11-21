<template>

  <q-card class="overflow-hidden">
    <!--
    <div class="contenedor"></div>
    -->
    <SidebarPlayerAlbumCover :normalImage="imageUrl" :smallImage="imageUrl"
      :animation="playerStore.sidebarTopArtAnimated" :animated="playerStore.isPlaying"
      @change="playerStore.toggleSidebarTopArtAnimationMode()">
    </SidebarPlayerAlbumCover>
    <!--
    <div style="width: 100%; height: 200px;">
      <SidebarPlayerAnalogVuMeter></SidebarPlayerAnalogVuMeter>
    </div>
    -->
    <SidebarPlayerSpectrumAnalyzer v-if="miniSpectrumAnalyzerSettings.isVisible" />
    <SidebarPlayerVolumeControl />
    <SidebarPlayerTrackInfo />
    <SidebarPlayerMainControls @changeTrack="refresh" />
    <SidebarPlayerSeekControl />
  </q-card>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";

import { default as SidebarPlayerAlbumCover } from "./SidebarPlayerAlbumCover.vue";
import { default as SidebarPlayerSpectrumAnalyzer } from "./SidebarPlayerSpectrumAnalyzer.vue";
import { default as SidebarPlayerAnalogVuMeter } from "./SidebarPlayerAnalogVuMeter.vue";
import { default as SidebarPlayerVolumeControl } from './SidebarPlayerVolumeControl.vue';
import { default as SidebarPlayerTrackInfo } from './SidebarPlayerTrackInfo.vue';
import { default as SidebarPlayerMainControls } from './SidebarPlayerMainControls.vue';
import { default as SidebarPlayerSeekControl } from './SidebarPlayerSeekControl.vue';

import { useAPI } from 'src/composables/useAPI';
import { usePlayerStore } from 'src/stores/player';
import { useCurrentPlaylistItemStore } from 'src/stores/currentPlaylistItem';
import { useMiniSpectrumAnalyzerSettingsStore } from "src/stores/miniSpectrumAnalyzerSettings";

import { useThumbnail } from "src/composables/useThumbnail";

const playerStore = usePlayerStore();
const currentPlaylistItemStore = useCurrentPlaylistItemStore();
const miniSpectrumAnalyzerSettings = useMiniSpectrumAnalyzerSettingsStore();

const { api } = useAPI();

const { getMediumURL } = useThumbnail();

const imageUrl = ref("images/vinyl.png");


const refresh = () => {
  imageUrl.value = null;
  api.file.getRandom().then((successResponse) => {
    currentPlaylistItemStore.setTrack(
      successResponse.data.file.id,
      successResponse.data.file.filename,
      successResponse.data.file.filesize,
      successResponse.data.file.mime,
      successResponse.data.file.trackInfo.playTimeSeconds,
      successResponse.data.file.trackInfo.title,
      successResponse.data.file.trackInfo.artist.name,
      successResponse.data.file.trackInfo.artist.mbId,
      successResponse.data.file.trackInfo.album.title,
      successResponse.data.file.trackInfo.album.mbId,
      successResponse.data.file.trackInfo.album.year,
      successResponse.data.file.trackInfo.album.artist.name,
      successResponse.data.file.trackInfo.album.artist.mbId,
      successResponse.data.file.trackInfo.image
    );
    playerStore.setAudioSource("/api2/file/raw/" + successResponse.data.file.id);
    if (playerStore.hasPreviousUserInteractions) {
      playerStore.play(true);
    }

    if (successResponse.data.file.trackInfo.album.mbId) {
      playerStore.setTmpTrack(successResponse.data.file.trackInfo);
      if (successResponse.data.file.trackInfo.image) {
        imageUrl.value = successResponse.data.file.trackInfo.image;
      } else if (successResponse.data.file.trackInfo.album.mbId) {
        imageUrl.value = getMediumURL(`https://coverartarchive.org/release/${successResponse.data.file.trackInfo.album.mbId}/front-500`);
      } else {
        imageUrl.value = null;

      }
      //imageUrl.value = "https://m.media-amazon.com/images/I/715kGo2MwhL._SL1200_.jpg";
    } else {
      imageUrl.value = null;
    }
  })
    .catch((errorResponse) => {
      console.error(errorResponse);
    });
};

onMounted(() => {
  refresh();
});

</script>

<style>
.contenedor {
  z-index: 200;
  width: 300px;
  height: 20px;
  background: linear-gradient(135deg, #dcdcdc 20%, #a9a9a9 50%, #dcdcdc 80%);
  position: relative;
  left: 390px;
  top: 20px;
  animation: rotar 5s infinite;
  transform-origin: left center;
}

@keyframes rotar {
  0% {
    transform: rotate(90deg);
  }

  100% {
    transform: rotate(120deg);
  }
}
</style>