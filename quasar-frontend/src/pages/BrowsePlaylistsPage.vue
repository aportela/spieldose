<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="list" label="Browse playlists" />
      </q-breadcrumbs>
      <q-card-section v-if="playlists">
        <q-input v-model="playlistName" rounded clearable type="search" outlined dense placeholder="Text condition"
          hint="Search playlists with name" :loading="loading" :disable="loading" @keydown.enter.prevent="search(true)"
          @clear="noPlaylistsFound = false; search(true)" :error="noPlaylistsFound"
          :errorMessage="'No playlists found with specified condition'">
          <template v-slot:prepend>
            <q-icon name="filter_alt" />
          </template>
          <template v-slot:append>
            <q-icon name="search" class="cursor-pointer" @click="search" />
          </template>
        </q-input>
        <div class="q-pa-lg flex flex-center" v-if="totalPages > 1">
          <q-pagination v-model="currentPageIndex" color="dark" :max="totalPages" :max-pages="5" boundary-numbers
            direction-links boundary-links @update:model-value="onPaginationChanged" :disable="loading" />
        </div>
        <div class="q-gutter-md row items-start">
          <q-card class="col-2 shadow-box shadow-10 q-mt-lg" bordered v-for="playlist in playlists" :key="playlist.id">
            <q-card-section>
              {{ playlist.name }}
            </q-card-section>
            <q-separator />
            <q-card-section>
              <div class="row">
                <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[0] || defaultImage"/></div>
                <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[1] || defaultImage"/></div>
                <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[2] || defaultImage"/></div>
              </div>
              <div class="row">
                <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[3] || defaultImage"/></div>
                <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[4] || defaultImage"/></div>
                <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[5] || defaultImage"/></div>
              </div>
              <div class="row">
                <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[6] || defaultImage"/></div>
                <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[7] || defaultImage"/></div>
                <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[8] || defaultImage"/></div>
              </div>
            </q-card-section>
            <q-separator />
            <q-card-section style="height: 120px;">
              <q-avatar v-for="n in 5" :key="n" size="80px" class="overlapping" :style="`left: ${n * 25}px`">
                <img :src="playlist.covers[n]" :class="'mosaic_cover_element rotate-' + (45 * (n + 3))" v-if="playlist.covers[n]" />
                <div v-else class="no_cover" :style="'background: ' + getRandomColor()"></div>
              </q-avatar>
            </q-card-section>
            <q-separator />
            <q-card-section>
              <q-icon name="edit" size="sm" title="edit playlist" class="cursor-pointer"></q-icon>
              <q-icon name="play_arrow" size="sm" title="play" class="cursor-pointer"></q-icon>
              {{ playlist.trackCount }} track/s
            </q-card-section>

          </q-card>
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<style>
div.no_cover {
  width: 100px;
  height: 100px;
}

img.mosaic_cover_element {
  width: 100%;
  max-width: 100%;
  height: auto;
  aspect-ratio: 1 / 1;
}

.overlapping {
  border: 2px solid white;
  position: absolute;
}
</style>

<script setup>

import { ref } from "vue";
import { api } from 'boot/axios'
import { useQuasar } from "quasar";

const $q = useQuasar();

const playlistName = ref(null);
const noPlaylistsFound = ref(false);
const loading = ref(false);
const playlists = ref([]);

const totalPages = ref(0);
const currentPageIndex = ref(1);

const defaultImage = 'images/vinyl.png';

const covers = ref([]);

// https://stackoverflow.com/a/1484514
function getRandomColor() {
  const allowed = "ABCDEF0123456789";
  let S = "#";
  while (S.length < 7) {
    S += allowed.charAt(Math.floor((Math.random() * 16) + 1));
  }
  return (S);
}

function search(resetPager) {
  if (resetPager) {
    currentPageIndex.value = 1;
  }
  noPlaylistsFound.value = false;
  loading.value = true;
  api.playlist.search(currentPageIndex.value, 32, { name: playlistName.value }).then((success) => {
    playlists.value = success.data.data.items;
    totalPages.value = success.data.data.pager.totalPages;
    if (playlistName.value && success.data.data.pager.totalResults < 1) {
      noPlaylistsFound.value = true;
    }
    loading.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: "API Error: error loading playlists",
      caption: "API Error: fatal error details: HTTP {" + error.response.status + "} ({" + error.response.statusText + "})"
    });
    loading.value = false;
  });
}

function onPaginationChanged(pageIndex) {
  currentPageIndex.value = pageIndex;
  search(false);
}

search(true);


</script>
