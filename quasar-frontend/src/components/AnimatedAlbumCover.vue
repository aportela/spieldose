<template>
  <div class="animated-album-cover-item">
    <div class="play-album">
      <q-img class="album-thumbnail" :class="{ 'album-thumbnail-animated': loaded || errors }" :src="imageSrc"
        width="174px" height="174px" spinner-color="pink" @load="onLoad" @error="onError">
      </q-img>
      <div class="album-actions">
        <q-icon class="cursor-pointer" name="add_box" size="80px" color="pink" title="Enqueue album" style="left: 10px;"
          @click="onEnqueue"></q-icon>
        <q-icon class="cursor-pointer" name="play_arrow" size="80px" color="pink" title="Play album" style="left: 80px;"
          @click="onPlay"></q-icon>
        <span class="clear: both;"></span>
      </div>
      <img class="vinyl no-cover" src="images/vinyl-medium.png" v-if="loaded || errors" />
    </div>
    <div class="album-info">
      <p class="album-name" v-if="title" :title="title">{{ title }}</p>
      <p v-if="artistName" class="artist-name">by <router-link :title="artistName"
          :to="{ name: 'artist', params: { name: artistName }, query: { mbid: artistMbId, tab: 'overview' } }">{{
            artistName }}</router-link> <span v-if="year">({{ year
  }})</span></p>
      <p v-else-if="year">({{ year }})</p>
    </div>
  </div>
</template>

<style>
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

div.album-actions {
  display: block;
  width: 174px;
  height: 80px;
  z-index: 2;
  top: 44px;
  position: absolute;

}

div.animated-album-cover-item div.play-album:hover div.album-actions {
  background: #ccc;
  opacity: 0.7;
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
  z-index: 2;
  color: #ccc;
  position: absolute;
  top: 0px;
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

const emit = defineEmits(['play', 'enqueue']);

const props = defineProps({
  title: String,
  artistMbId: String,
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

function onEnqueue() {
  emit('enqueue');
}

</script>
