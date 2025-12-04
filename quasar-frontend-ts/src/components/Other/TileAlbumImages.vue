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

<script setup lang="ts">

import { ref, onMounted } from "vue";
//import { api } from "src/composables/api";

// vinyl svg credits: https://commons.wikimedia.org/wiki/File:Vinyl_record.svg
const defaultImage = 'vectors/Vinyl_record.svg';

const images = ref<string[]>([]);

const rowCount = 12;
const columnCount = 12;

const rows = [...Array(rowCount).keys()];
const columns = [...Array(columnCount).keys()];

// https://stackoverflow.com/a/1484514
const getRandomColor = (): string => {
  const allowed = "ABCDEF0123456789";
  let S = "#";
  while (S.length < 7) {
    S += allowed.charAt(Math.floor(Math.random() * 16));
  }
  return S;
};

const getImageSourceFromIndex = (index: number): string => {
  if (index < images.value.length) {
    return (images.value[index]!);
  } else {
    return (defaultImage);
  }
};

const loadRandomAlbumImages = (): void => {
  /*
  api.album.getSmallRandomCovers(144).then(response => {
    images.value = Array.isArray(response.data.coverURLs) ? response.data.coverURLs : [];
  }).catch(error => {
    console.error(error.response);
  });
  */
};

const onImageError = (event) => {
  event.target.src = defaultImage;
};

onMounted(() => {
  loadRandomAlbumImages();
});

</script>

<style lang="css">
div#spieldose-album-cover-tiles-container {
  box-shadow: inset 24px 4px 64px -24px rgba(71, 71, 71, 1);
  background-color: #666;
  height: 100vh;
  overflow: hidden;
  filter: blur(3px);
  opacity: 0.8;
}

img.spieldose-album-cover-tile {
  width: 100%;
  max-width: 100%;
  height: 100%;
  aspect-ratio: 1 / 1;
}
</style>