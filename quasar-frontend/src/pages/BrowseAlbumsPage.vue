<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="album" label="Browse albums" />
      </q-breadcrumbs>
      <q-card-section v-if="albums">
        <div class="row q-gutter-xs">
          <div class="col">
            <q-input v-model="albumTitle" clearable type="search" outlined dense placeholder="Text condition"
              hint="Search albums with title" :loading="loading" :disable="loading" @keydown.enter.prevent="search(true)"
              @clear="noAlbumsFound = false; search(true)" :error="noAlbumsFound"
              :errorMessage="'No albums found with specified condition'">
              <template v-slot:prepend>
                <q-icon name="filter_alt" />
              </template>
              <template v-slot:append>
                <q-icon name="search" class="cursor-pointer" @click="search" />
              </template>
            </q-input>
          </div>
          <div class="col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4">
            <q-select outlined dense v-model="sortField" :options="sortFieldValues" options-dense label="Sort field"
              @update:model-value="search(true)">
              <template v-slot:selected-item="scope">
                {{ scope.opt.label }}
              </template>
            </q-select>
          </div>
          <div class="col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4">
            <q-select outlined dense v-model="sortOrder" :options="sortOrderValues" options-dense label="Sort order"
              @update:model-value="search(true)">
              <template v-slot:selected-item="scope">
                {{ scope.opt.label }}
              </template>
            </q-select>
          </div>
        </div>
        <div class="q-pa-lg flex flex-center" v-if="totalPages > 1">
          <q-pagination v-model="currentPageIndex" color="dark" :max="totalPages" :max-pages="5" boundary-numbers
            direction-links boundary-links @update:model-value="onPaginationChanged" :disable="loading" />
        </div>
        <div class="q-gutter-md row items-start">
          <AnimatedAlbumCover v-for="album in albums" :key="album.mbId || album.title" :image="album.image"
            :title="album.title" :artistName="album.artist.name" :year="album.year" @play="onPlayAlbum(album)">
          </AnimatedAlbumCover>
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup>

import { ref } from "vue";
import { api } from 'boot/axios'
import { useQuasar } from "quasar";
import { default as AnimatedAlbumCover } from "components/AnimatedAlbumCover.vue";
import { usePlayer } from 'stores/player'
import { useCurrentPlaylistStore } from 'stores/currentPlaylist'


const $q = useQuasar();
const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();

const albumTitle = ref(null);
const sortFieldValues = [
  {
    label: 'Title',
    value: 'title'
  },
  {
    label: 'Artist',
    value: 'albumArtistName'
  }
];

const sortField = ref(sortFieldValues[0]);

const sortOrderValues = [
  {
    label: 'Ascending',
    value: 'ASC'
  },
  {
    label: 'Descending',
    value: 'DESC'
  }
];

const sortOrder = ref(sortOrderValues[0]);

const noAlbumsFound = ref(false);
const loading = ref(false);
const albums = ref([]);

const totalPages = ref(0);
const currentPageIndex = ref(1);

function search(resetPager) {
  if (resetPager) {
    currentPageIndex.value = 1;
  }
  noAlbumsFound.value = false;
  loading.value = true;
  api.album.search(albumTitle.value, currentPageIndex.value, 32, sortField.value.value, sortOrder.value.value).then((success) => {
    albums.value = success.data.data.items.map((item) => {
      item.image = item.covers.small;
      return (item);
    });
    totalPages.value = success.data.data.pager.totalPages;
    if (albumTitle.value && success.data.data.pager.totalResults < 1) {
      noAlbumsFound.value = true;
    }
    loading.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: "API Error: error loading albums",
      caption: "API Error: fatal error details: HTTP {" + error.response.status + "} ({" + error.response.statusText + "})"
    });
    loading.value = false;
  });
}

function onPaginationChanged(pageIndex) {
  currentPageIndex.value = pageIndex;
  search(false);
}

function onPlayAlbum(album) {
  player.interact();
  loading.value = true;
  api.track.search({ albumMbId: album.mbId }, 1, 0, false, 'trackNumber', 'ASC').then((success) => {
    currentPlaylist.saveElements(success.data.data.items.map((item) => { return ({ track: item }); }));
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
  });
}

search(true);

</script>
