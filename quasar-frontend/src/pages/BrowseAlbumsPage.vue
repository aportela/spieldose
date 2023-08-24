<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="album" label="Browse albums" />
      </q-breadcrumbs>

      <q-card-section v-if="albums">

        <div class="q-pa-lg flex flex-center" v-if="totalPages > 1">
          <q-pagination v-model="currentPageIndex" color="dark" :max="totalPages" :max-pages="5" boundary-numbers
            direction-links boundary-links @update:model-value="onPaginationChanged" :disable="loading" />
        </div>
        <div class="q-gutter-md row items-start">
          <div class="browse-album-item" v-for="album in albums" :key="album.title">
                    <a class="play-album">
                        <img class="album-thumbnail" :src="album.image || 'images/image-album-not-set.png'" />
                        <i class="fas fa-play fa-4x"></i>
                        <img class="vinyl no-cover" src="images/vinyl.png" />
                    </a>
                    <div class="album-info">
                        <p class="album-name">{{ album.title }}</p>
                        <p v-if="album.artist" class="artist-name">by <router-link :to="{ name: 'artist', params: { name: album.artist.name }}">{{ album.artist.name }}</router-link><span v-show="album.year"> ({{ album.year }})</span></p>
                        <p v-else class="artist-name">by unknownArtist <span v-show="album.year"> ({{ album.year }})</span></p>
                    </div>
                </div>
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<style scoped>
/* album thumb */

div.browse-album-item,
div.playlist-item,
div.path-item {
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

div.browse-album-item a.play-album img.album-thumbnail,
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

div.browse-album-item a.play-album img.album-thumbnail {
    -webkit-transition: all 0.2s ease-out;
    -moz-transition: all 0.2s ease-out;
    -o-transition: all 0.2s ease-out;
    transition: all 0.2s ease-out;
    background: #000;
}

div.browse-album-item a.play-album:hover img.album-thumbnail {
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

import { ref, watch, computed } from "vue";
import { api } from 'boot/axios'

const loading = ref(false);
const albums = ref([]);

const totalPages = ref(10);
const currentPageIndex = ref(1);

function search() {
  loading.value = true;
  api.album.search(currentPageIndex.value, 32, {}).then((success) => {
    albums.value = success.data.data.items;
    totalPages.value = success.data.data.pager.totalPages;
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
  });
}

function onPaginationChanged(pageIndex) {
  currentPageIndex.value = pageIndex;
  search();
}

search();

</script>
