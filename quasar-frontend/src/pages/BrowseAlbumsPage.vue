<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="album" label="Browse albums" />
      </q-breadcrumbs>
      <q-card-section v-if="albums">
        <q-input v-model="albumTitle" rounded clearable type="search" outlined dense placeholder="Text condition"
          hint="Search albums with title" :loading="loading" :disable="loading" @keydown.enter.prevent="search" @clear="noAlbumsFound = false" :error="noAlbumsFound" :errorMessage="'No albums found with specified condition'">
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
          <AnimatedAlbumCover v-for="album in albums" :key="album.title" :album="album"></AnimatedAlbumCover>
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>


<script setup>

import { ref, watch, computed } from "vue";
import { api } from 'boot/axios'
import { default as AnimatedAlbumCover } from "components/AnimatedAlbumCover.vue";

const albumTitle = ref(null);
const noAlbumsFound = ref(false);
const loading = ref(false);
const albums = ref([]);

const totalPages = ref(10);
const currentPageIndex = ref(1);

function search() {
  noAlbumsFound.value = false;
  loading.value = true;
  api.album.search(currentPageIndex.value, 32,{ title: albumTitle.value }).then((success) => {
    albums.value = success.data.data.items;
    totalPages.value = success.data.data.pager.totalPages;
    if (albumTitle.value && success.data.data.pager.totalResults < 1) {
      noAlbumsFound.value = true;
    }
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
