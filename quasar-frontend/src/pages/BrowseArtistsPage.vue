<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="person" label="Browse artists" />
      </q-breadcrumbs>
      <q-card-section v-if="artists">
        <div class="row q-gutter-xs">
          <div class="col">
            <q-input v-model="artistName" clearable type="search" outlined dense placeholder="Text condition"
              hint="Search artists with name" :loading="loading" :disable="loading" @keydown.enter.prevent="search(true)"
              @clear="noArtistsFound = false; search(true)" :error="noArtistsFound"
              :errorMessage="'No artists found with specified condition'">
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
          <router-link :to="{ name: 'artist', params: { name: artist.name } }" v-for="artist in artists" :key="artist">
            <q-img img-class="artist_image" :src="artist.image || '#'" width="250px" height="250px" fit="cover">
              <div class="absolute-bottom text-subtitle1 text-center">
                {{ artist.name }}
                <p class="text-caption q-mb-none">{{ artist.totalTracks + " " + (artist.totalTracks > 1 ? 'tracks' :
                  'track') }}</p>
              </div>
              <template v-slot:error>
                <div class="absolute-full flex flex-center bg-grey-3 text-dark">
                  <div class="absolute-bottom text-subtitle1 text-center bg-grey-5 q-py-md">
                    {{ artist.name }}
                    <p class="text-caption q-mb-none">{{ artist.totalTracks + " " + (artist.totalTracks > 1 ? 'tracks' :
                      'track') }}</p>
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

<style>
img.artist_image {
  opacity: 0.5;
  -webkit-filter: grayscale(100%);
  /* Safari 6.0 - 9.0 */
  filter: grayscale(100%) blur(2px);
  transition: filter 0.2s ease-in;
}

img.artist_image:hover {
  opacity: 1;
  -webkit-filter: none;
  /* Safari 6.0 - 9.0 */
  filter: none;
  transition: filter 0.2s ease-out;

}
</style>

<script setup>

import { ref } from "vue";
import { api } from 'boot/axios'
import { useQuasar } from "quasar";

const $q = useQuasar();
const artistName = ref(null);
const noArtistsFound = ref(false);
const loading = ref(false);
const artists = ref([]);

const sortFieldValues = [
  {
    label: 'Name',
    value: 'name'
  },
  {
    label: 'Total tracks',
    value: 'totalTracks'
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

const totalPages = ref(0);

const currentPageIndex = ref(1);

function search(resetPager) {
  if (resetPager) {
    currentPageIndex.value = 1;
  }
  noArtistsFound.value = false;
  loading.value = true;
  api.artist.search(artistName.value, currentPageIndex.value, 32, sortField.value.value, sortOrder.value.value).then((success) => {
    artists.value = success.data.data.items;
    totalPages.value = success.data.data.pager.totalPages;
    if (artistName.value && success.data.data.pager.totalResults < 1) {
      noArtistsFound.value = true;
    }
    loading.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: "API Error: error loading artists",
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
