<template>
  <div class="browse-album-item">
    <a class="play-album">
      <q-img :class="{ 'album-thumbnail': true, 'album-thumbnail-animated': loaded ||errors }" :src="imageURL" width="174px" height="174px" v-if="imageURL" spinner-color="pink" @load="onLoad" @error="onError">
      </q-img>
      <q-img :class="{ 'album-thumbnail': true, 'album-thumbnail-animated': loaded ||errors }" src="images/image-album-not-set.png" v-else />
      <!--
      <i class="fas fa-play fa-4x"></i>
      -->
      <img class="vinyl no-cover" src="images/vinyl.png" v-if="loaded || errors"/>
    </a>
    <div class="album-info">
      <p class="album-name">{{ album.title }}</p>
      <p v-if="album.artist" class="artist-name">by <router-link
          :to="{ name: 'artist', params: { name: album.artist.name } }">{{ album.artist.name }}</router-link><span
          v-show="album.year"> ({{ album.year }})</span></p>
      <p v-else class="artist-name">by unknownArtist <span v-show="album.year"> ({{ album.year }})</span></p>
    </div>
  </div>
</template>

<style scoped>
/* album thumb */

div.browse-album-item {
  width: 174px;
  float: left;
  margin: 0px 24px;
  margin-bottom: 16px;
}

div.browse-album-item a.play-album {
  width: 174px;
  height: 174px;
  display: block;
  position: relative;
  border-radius: 4px;
  overflow: hidden;
}

div.browse-album-item a.play-album img.no-cover {
  display: block !important;
}

div.browse-album-item a.play-album .album-thumbnail,
div.browse-album-item a img.vynil {
  width: 174px;
  height: 174px;
  border: 0px;
  position: absolute;
  top: 0px;
  left: 0px;
  z-index: 1;
}

/* album cover effect (hover -> move to left) */

div.browse-album-item a.play-album .album-thumbnail-animated {
  -webkit-transition: all 0.2s ease-out;
  -moz-transition: all 0.2s ease-out;
  -o-transition: all 0.2s ease-out;
  transition: all 0.2s ease-out;
  /* background: #000; */
}

div.browse-album-item a.play-album:hover .album-thumbnail-animated {
  left: -80px;
}

/* album cover effect (hover -> move to left) */

/* album (internal) vinyl effect (hover -> rotate) */

div.browse-album-item a.play-album img.vinyl {
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

div.browse-album-item a.play-album:hover img.vinyl {
  z-index: 0;
  display: block;
  -webkit-transform: rotate(360deg);
  -moz-transform: rotate(360deg);
  -o-transform: rotate(360deg);
}

/* album (internal) vinyl effect (hover -> rotate) */

/* play icon (hover cover) */

div.browse-album-item a.play-album i {
  display: none;
  position: absolute;
  top: 60px;
  left: 72px;
  z-index: 2;
  color: #ccc;
}

div.browse-album-item a.play-album:hover i {
  display: block;
}

/* artist / album name (below cover) */

div.browse-album-item div.album_info {
  padding: 8px;
  overflow: hidden;
}

div.browse-album-item div.album-info p {
  margin: 0px;
  font-size: 13px;
  font-family: Tahoma;
  width: 100%;
  height: 18px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

div.browse-album-item div.album-info p. * {
  color: #939aa0;
}

/* album thumb */
</style>
<script setup>

import { ref, computed } from "vue";

const props = defineProps({
  album: Object,
});

const loading = ref(false);
const loaded = ref(false);
const errors = ref(false);

const imageURL = computed(() => {
  return(! errors.value ? props.album.image: null)
});

if (props.album.image) {
  loading.value = true;
}

function onLoad() {
  loading.value = false;
  loaded.value = true;
}

function onError() {
  loading.value = false;
  errors.value = true;
}
</script>
