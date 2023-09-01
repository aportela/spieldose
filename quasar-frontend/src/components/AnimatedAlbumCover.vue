<template>
  <div class="animated-album-cover-item">
    <div class="play-album" title="Play album" @click="onPlay">
      <q-img class="album-thumbnail" :class="{ 'album-thumbnail-animated': loaded || errors }" :src="imageSrc"
        width="174px" height="174px" spinner-color="pink" @load="onLoad" @error="onError">
      </q-img>
      <q-icon name="play_arrow" class="browse_album_play_icon" size="128px" color="white"></q-icon>
      <!--
      <i class="fas fa-play fa-4x"></i>
      -->
      <img class="vinyl no-cover" src="images/vinyl.png" v-if="loaded || errors" />
    </div>
    <div class="album-info">
      <p class="album-name" v-if="title" :title="title">{{ title }}</p>
      <p v-if="artistName" class="artist-name">by <router-link :title="artistName"
          :to="{ name: 'artist', params: { name: artistName } }">{{ artistName }}</router-link> <span v-if="year">({{ year
          }})</span></p>
      <p v-else-if="year">({{ year }})</p>
    </div>
  </div>
</template>

<style scoped>
/* album thumb */

div.animated-album-cover-item {
  width: 174px;
  float: left;
  margin: 0px 24px;
  margin-bottom: 16px;
}

div.animated-album-cover-item div.play-album {
  width: 174px;
  height: 174px;
  display: block;
  position: relative;
  border-radius: 4px;
  overflow: hidden;
  cursor: pointer;
}

div.animated-album-cover-item div.play-album img.no-cover {
  display: block !important;
}

div.animated-album-cover-item div.play-album .album-thumbnail,
div.animated-album-cover-item a img.vynil {
  width: 174px;
  height: 174px;
  border: 0px;
  position: absolute;
  top: 0px;
  left: 0px;
  z-index: 1;
}

/* album cover effect (hover -> move to left) */

div.animated-album-cover-item div.play-album .album-thumbnail-animated {
  -webkit-transition: all 0.2s ease-out;
  -moz-transition: all 0.2s ease-out;
  -o-transition: all 0.2s ease-out;
  transition: all 0.2s ease-out;
  /* background: #000; */
}

div.animated-album-cover-item div.play-album:hover .album-thumbnail-animated {
  left: -80px;
}

/* album cover effect (hover -> move to left) */

/* album (internal) vinyl effect (hover -> rotate) */

div.animated-album-cover-item div.play-album img.vinyl {
  z-index: 0;
  display: none;
  width: 174px;
  height: 174px;
  -webkit-transition: all 1s ease-out;
  -moz-transition: all 1s ease-out;
  -o-transition: all 1s ease-out;
  transition: all 1s ease-out;
  -webkit-transition-property: -webkit-transform;
  -moz-transition-property: -moz-transform;
  -o-transition-property: -o-transform;
  transition-property: transform;
}

div.animated-album-cover-item div.play-album:hover img.vinyl {
  z-index: 0;
  display: block;
  -webkit-transform: rotate(360deg);
  -moz-transform: rotate(360deg);
  -o-transform: rotate(360deg);
}

/* album (internal) vinyl effect (hover -> rotate) */

/* play icon (hover cover) */

div.animated-album-cover-item div.play-album i {
  display: none;
  position: absolute;
  top: 24px;
  left: 24px;
  z-index: 2;
  color: #ccc;
}

div.animated-album-cover-item div.play-album:hover i {
  display: block;
}

/* artist / album name (below cover) */

div.animated-album-cover-item div.album-info {
  margin-top: 4px;
  text-align: center;
}

div.animated-album-cover-item div.album-info p {
  margin: 0px;
  font-size: 13px;
  font-family: Tahoma;
  height: 18px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* album thumb */
</style>

<script setup>

import { ref, computed } from "vue";

const emit = defineEmits(['play']);

const props = defineProps({
  title: String,
  artistName: String,
  year: Number,
  image: String
});

const loaded = ref(false);
const errors = ref(false);

const imageSrc = computed(() => {
  return ((props.image && !errors.value) ? props.image : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=");
});

function onLoad() {
  loaded.value = true;
}

function onError() {
  errors.value = true;
}

function onPlay() {
  emit('play');
}

</script>
