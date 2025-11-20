<template>

  <q-card>
    <div class="contenedor"></div>
    <SidebarPlayerAlbumCover :normalImage="imageUrl" :smallImage="imageUrl"
      :animation="playerStore.sidebarTopArtAnimated" :animated="playerStore.isPlaying"
      @change="playerStore.toggleSidebarTopArtAnimationMode()">
    </SidebarPlayerAlbumCover>
    <SidebarPlayerSpectrumAnalyzer />
    <SidebarPlayerVolumeControl />
    <SidebarPlayerTrackInfo />
    <SidebarPlayerMainControls @changeTrack="refresh" />
    <SidebarPlayerSeekControl />
  </q-card>
</template>

<script setup>
import { ref, onMounted } from "vue";

import { default as SidebarPlayerAlbumCover } from "./SidebarPlayerAlbumCover.vue";
import { default as SidebarPlayerSpectrumAnalyzer } from "./SidebarPlayerSpectrumAnalyzer.vue";
import { default as SidebarPlayerVolumeControl } from './SidebarPlayerVolumeControl.vue';
import { default as SidebarPlayerTrackInfo } from './SidebarPlayerTrackInfo.vue';
import { default as SidebarPlayerMainControls } from './SidebarPlayerMainControls.vue';
import { default as SidebarPlayerSeekControl } from './SidebarPlayerSeekControl.vue';

import { useAPI } from 'src/composables/useAPI';
import { usePlayerStore } from 'src/stores/player';
import { useThumbnail } from "src/composables/useThumbnail";

const playerStore = usePlayerStore();

const { api } = useAPI();

const { getMediumURL } = useThumbnail();

const imageUrl = ref("images/vinyl.png");

const refresh = () => {
  imageUrl.value = null;
  api.file.getRandom().then((successResponse) => {
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
  width: 200px;
  height: 20px;
  background: linear-gradient(135deg, #dcdcdc 20%, #a9a9a9 50%, #dcdcdc 80%);
  position: relative;
  left: 400px;
  top: 10px;
  animation: rotar 5s infinite;
  transform-origin: left center;
}

@keyframes rotar {
  0% {
    transform: rotate(90deg);
  }

  100% {
    transform: rotate(100deg);
  }
}
</style>
