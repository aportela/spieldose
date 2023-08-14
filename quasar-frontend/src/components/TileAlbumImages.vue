<template>
  <div class="tile is-ancestor row" id="container_tiles" v-if="!loading">
    <div class="row" v-for="column in [0, 1, 2, 3, 4, 5]" :key="column">
      <div class="col-2" v-for="row in [0, 1, 2, 3, 4, 5]" :key="row">
        <img v-if="covers && covers.length > 0" :src="getImageSource(covers[(5 * column) + row])" style="width:100%"
          @error="$event.target.src = '/images/vinyl.png'">
        <div v-else style="width: 100%; height: auto;" :style="'background: ' + getRandomColor()"></div>
      </div>
    </div>
  </div>
</template>

<style>
div#container_tiles
{
    box-shadow: inset 24px 4px 64px -24px rgba(71,71,71,1);
    background-color: #666;
    height: 100vh;
    overflow: hidden;
    filter: blur(6px);
    transition: filter 0.3s ease-in;
    opacity: 0.5;
}
.tile:not(.is-child) {
  display: flex;
}

.tile {
  align-items: stretch;
}

.tile.is-vertical
{
  flex-direction: column;
}

</style>

<script setup>

import { ref } from "vue";

const loading = ref(false);

const covers = ref([]);

// https://stackoverflow.com/a/1484514
function getRandomColor() {
  var letters = '0123456789ABCDEF';
  var color = '#';
  for (var i = 0; i < 6; i++) {
    color += letters[Math.floor(Math.random() * 16)];
  }
  return color;
}
function getImageSource(img) {
  if (img) {
    if (img.hash) {
      return ('api/thumbnail?hash=' + img.hash);
    } else if (img.url) {
      return ('api/thumbnail?url=' + img.url);
    } else {
      return (null);
    }
  }
}
function loadRandomAlbumImages() {
  this.loading = true;
  spieldoseAPI.album.getRandomAlbumCovers(32, (response) => {
    if (response.status == 200) {
      if (response.data.covers.length == 32) {
        this.covers = response.data.covers.map((cover, idx) => { cover.id = idx; return (cover); });
      }
    }
    this.loading = false;
  });
}
</script>
