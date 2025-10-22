<template>
  <div id="spieldose-album-cover-tiles-container">
    <div class="row" v-for="row, rowIndex in rows" :key="row">
      <div class="col-md-3 col-lg-2 col-xl-1" v-for="column in columns" :key="column"
        :style="'background-color: ' + getRandomColor() + ';'">
        <img class="spieldose-album-cover-tile" :src="getImageSourceFromIndex((rows.length * rowIndex) + column)"
          v-if="images.length > 0" @error="onImageError($event)">
        <img class="spieldose-album-cover-tile" :src="defaultImage" v-else>
      </div>
    </div>
  </div>
</template>

<script setup>

import { ref } from "vue";
import { useAPI } from "src/composables/useAPI";

const { api } = useAPI();

/**
  * Vinyl disc icon credits: Jordan Green (http://www.jordangreenphoto.com/)
  * https://jordygreen.deviantart.com/art/Vinyl-Disc-Icon-Updated-57968239
*/

const defaultImage = 'images/vinyl-medium.png';

const images = ref([]);

const rows = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
const columns = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];

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
  if (index < images.value.length) {
    return (images.value[index]);
  } else {
    return (defaultImage);
  }
}

function loadRandomAlbumImages() {
  api.album.getSmallRandomCovers(144).then(response => {
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

<style lang="css">
div#spieldose-album-cover-tiles-container {
  box-shadow: inset 24px 4px 64px -24px rgba(71, 71, 71, 1);
  background-color: #666;
  height: 100vh;
  overflow: hidden;
  filter: blur(4px);
  opacity: 0.8;
}

img.spieldose-album-cover-tile {
  width: 100%;
  max-width: 100%;
  height: 100%;
  aspect-ratio: 1 / 1;
}
</style>