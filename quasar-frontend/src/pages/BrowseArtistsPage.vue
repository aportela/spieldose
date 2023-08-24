<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="person" label="Browse artists" />
      </q-breadcrumbs>

      <q-card-section v-if="artists">

        <q-input v-model="artistName" rounded clearable type="search" outlined dense placeholder="Text condition"
          hint="Search artists with name" :loading="loading" :disable="loading" @keydown.enter.prevent="search" @clear="noArtistsFound = false" :error="noArtistsFound" :errorMessage="'No artists found with specified condition'">
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
          <router-link :to="{ name: 'artist', params: { name: artist.name } }" v-for="artist in artists" :key="artist">
            <q-img :src="artist.image || '#'" width="250px" height="250px" fit="cover">
              <div class="absolute-bottom text-subtitle1 text-center">
                {{ artist.name }}
              </div>
              <template v-slot:error>
                <div class="absolute-full flex flex-center bg-grey-3 text-dark">
                  <div class="absolute-bottom text-subtitle1 text-center bg-grey-5 q-py-md">
                    {{ artist.name }}
                  </div>
                </div>
              </template>
            </q-img>
          </router-link>
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup>

import { ref } from "vue";
import { api } from 'boot/axios'

const artistName = ref(null);
const noArtistsFound = ref(false);
const loading = ref(false);
const artists = ref([]);

const totalPages = ref(10);
const currentPageIndex = ref(1);

function search() {
  noArtistsFound.value = false;
  loading.value = true;
  api.artist.search(currentPageIndex.value, 32, { name: artistName.value }).then((success) => {
    artists.value = success.data.data.items;
    totalPages.value = success.data.data.pager.totalPages;
    if (artistName.value && success.data.data.pager.totalResults < 1) {
      noArtistsFound.value = true;
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
