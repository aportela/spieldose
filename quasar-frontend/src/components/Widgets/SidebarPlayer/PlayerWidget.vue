<template>

  <q-card>
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
  api.file.getRandom().then((successResponse) => {
    playerStore.setAudioSource("/api2/file/raw/" + successResponse.data.file.id);
    if (successResponse.data.file.trackInfo.album.mbId) {
      playerStore.setTmpTrack(successResponse.data.file.trackInfo);
      imageUrl.value = getMediumURL(`https://coverartarchive.org/release/${successResponse.data.file.trackInfo.album.mbId}/front-500`);
      //imageUrl.value = "https://m.media-amazon.com/images/I/715kGo2MwhL._SL1200_.jpg";
    } else {
      imageUrl.value = "images/vinyl.png";
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
