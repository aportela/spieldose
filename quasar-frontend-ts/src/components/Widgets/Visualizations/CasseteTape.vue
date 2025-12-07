<template>
  <div class="cassette-container">
    <div class="cassette-wheel" :class="{ 'cassette-wheel-animated': playerStore.isPlaying }"></div>
    <div class="cassette-wheel" :class="{ 'cassette-wheel-animated': playerStore.isPlaying }"></div>
  </div>

</template>

<script setup lang="ts">
import { usePlayerStore } from 'src/stores/player';

const playerStore = usePlayerStore();
</script>

<style lang="css">
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