<template>
  <div id="spieldose-album-cover-tiles-container">
    <div class="row" v-for="row in [0, 1, 2, 3, 4, 5, 6]" :key="row">
      <div class="col-2" v-for="column in [0, 1, 2, 3, 4, 5]" :key="column"
        :style="'background-color: ' + getRandomColor() + ';'">
        <img class="spieldose-album-cover-tile" :src="getImageSourceFromIndex((6 * row) + column)"
          v-if="images.length > 0" @error="onImageError($event)">
        <img class="spieldose-album-cover-tile" :src="defaultImage" v-else>
      </div>
    </div>
  </div>
</template>

<style>
div#spieldose-album-cover-tiles-container {
  box-shadow: inset 24px 4px 64px -24px rgba(71, 71, 71, 1);
  background-color: #666;
  height: 100vh;
  overflow: hidden;
  filter: blur(6px);
  transition: filter 0.3s ease-in;
  opacity: 0.5;
}

img.spieldose-album-cover-tile {
  width: 100%;
  max-width: 100%;
  height: auto;
  aspect-ratio: 1 / 1;
}
</style>

<script setup>

import { ref } from "vue";
import { api } from 'boot/axios'

/**
  * Vinyl disc icon credits: Jordan Green (http://www.jordangreenphoto.com/)
  * https://jordygreen.deviantart.com/art/Vinyl-Disc-Icon-Updated-57968239
*/

const defaultImage = 'images/vinyl.png';

const images = ref([]);

// https://stackoverflow.com/a/1484514
function getRandomColor() {
  const allowed = "ABCDEF0123456789";
  let S = "#";
  while (S.length < 7) {
    S += allowed.charAt(Math.floor((Math.random() * 16) + 1));
  }
  return (S);
}

function getImageSourceFromIndex(index) {
  if (index < this.images.length) {
    return (this.images[index]);
  } else {
    return (this.defaultImage);
  }
}

function loadRandomAlbumImages() {
  api.album.getSmallRandomCovers(42).then(response => {
    images.value = Array.isArray(response.data.coverURLs) ? response.data.coverURLs : [];
  }).catch(error => {
    console.error(error.response);
  });
}

function onImageError(event) {
  event.target.src = defaultImage;
}

loadRandomAlbumImages();

</script>
