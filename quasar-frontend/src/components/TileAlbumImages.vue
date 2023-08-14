<template>
  <div id="container_tiles" v-if="!loading">
    <div v-for="column in [0, 1, 2, 3, 4, 5]" :key="column">
      <div class="row" v-for="column in [0,1,2,3,4,5]" :key="column">
          <div class="col-2" v-for="row in [0,1,2,3,4,5]" :key="row" :style="'background-color: ' + getRandomColor() + ';'">
            <img class="album-cover-tile" v-if="imageURLs.length > 0" :src="getImgSource((5 * column) + row)"
          @error="onImageError($event)">
          <img class="album-cover-tile" :src="defaultImage" v-else>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
div#container_tiles {
  box-shadow: inset 24px 4px 64px -24px rgba(71, 71, 71, 1);
  background-color: #666;
  height: 100vh;
  overflow: hidden;
  filter: blur(6px);
  transition: filter 0.3s ease-in;
  opacity: 0.5;
}

/**
  * Vinyl disc icon credits: Jordan Green (http://www.jordangreenphoto.com/)
  * https://jordygreen.deviantart.com/art/Vinyl-Disc-Icon-Updated-57968239
*/

img {
  height: auto;
  max-width: 100%;
}

img.album-cover-tile {
  width: 100%;
  aspect-ratio: 1 / 1;
}

</style>

<script setup>

import { ref } from "vue";
const defaultImage = 'images/vinyl.png';
const imageURLs = ref([]);
const loading = ref(false);

function getRandomColor() {
  const allowed = "ABCDEF0123456789";
  let S = "#";
  while (S.length < 7) {
    S += allowed.charAt(Math.floor((Math.random() * 16) + 1));
  }
  return (S);
}

function getImgSource(index) {
  if (index < this.imageURLs.length) {
    return (this.imageURLs[index]);
  } else {
    return (this.defaultImage);
  }
}

function loadRandomAlbumImages() {
  this.$api.album.getRandomAlbumCoverThumbnails().then(response => {
    if (response.data.coverURLs.length >= 32) {
      this.imageURLs = Array.isArray(response.data.coverURLs) ? response.data.coverURLs : [];
    }
  }).catch(error => {
    this.imageURLs = [];
  });
}

function onImageError(event) {
  event.target.src = this.defaultImage;
}

</script>
