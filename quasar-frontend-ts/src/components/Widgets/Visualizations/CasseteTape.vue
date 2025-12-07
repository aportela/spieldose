<template>
  <div class="cassette-container">
    <div class="cassette-wheel" :class="{ 'cassette-wheel-animated': playerStore.isPlaying }"></div>
    <div class="cassette-wheel" :class="{ 'cassette-wheel-animated': playerStore.isPlaying }"></div>
  </div>
  <div class="label gochi-hand-regular" v-if="currentPlaylistItemStore.isTrack">
    <span class="artist">{{ currentPlaylistItemStore.trackAlbumArtistName }}</span>
    <span class="album">{{ currentPlaylistItemStore.trackTitle }}</span>
  </div>
</template>

<script setup lang="ts">
import { usePlayerStore } from 'src/stores/player';
import { useCurrentPlaylistItemStore } from "src/stores/currentPlaylistItem";
const currentPlaylistItemStore = useCurrentPlaylistItemStore();
const playerStore = usePlayerStore();
</script>

<style lang="css">
@import url('https://fonts.googleapis.com/css2?family=Gochi+Hand&display=swap');

.label {
  position: relative;
  top: -220px;
  left: 64px;
  z-index: 3;

}

.artist {
  position: absolute;
  top: -16px;
  transform-origin: center;
  transform: rotate(-3deg);
  display: inline-block;
  width: 236px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  text-align: center;
}

.album {
  position: absolute;
  top: 3px;
  left: 16px;
  transform-origin: center;
  transform: rotate(-2deg);
  display: inline-block;
  width: 296px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  text-align: center;
}

.gochi-hand-regular {
  font-family: "Gochi Hand", cursive;
  font-weight: 400;
  font-style: normal;
  font-size: 22px;
  color: #04008f;
}

/*
  cassete vector credits: Patrick Schwarz (nablagrange) at https://pixabay.com/vectors/cassette-music-magnetic-tape-7576061/
  */
.cassette-container {
  width: 410px;
  height: 250px;
  background-image: url('vectors/cassette.svg');
  background-position: center;
  background-size: cover;
  position: relative;
  overflow: hidden;
  z-index: 1;
}

.cassette-wheel {
  position: absolute;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background-color: transparent;
  background-image: url('vectors/cassette.svg');
  background-size: 410px 250px;
  background-repeat: no-repeat;

  z-index: 2;
}

.cassette-wheel-animated {
  animation: spin 3s infinite linear;
}

.cassette-wheel:first-child {
  left: 97px;
  top: 102px;
  transform: translateY(-50%) rotate(0deg);
  background-position: -104px -79px;
}

.cassette-wheel:last-child {
  right: 97px;
  top: 103px;
  transform: translateY(-50%) rotate(0deg);
  background-position: -256px -79px;
}

@keyframes spin {
  0% {
    transform: translateY(-50%) rotate(0deg);
  }

  100% {
    transform: translateY(-50%) rotate(360deg);
  }
}
</style>
