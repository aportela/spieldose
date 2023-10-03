<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <q-breadcrumbs-el icon="person" label="Browse artists" />
    </q-breadcrumbs>
    <q-card-section>
      <div class="row q-gutter-xs q-mb-md">
        <div class="col">
          <q-input v-model="artistName" clearable type="search" outlined dense placeholder="Text condition"
            hint="Search artists with name" :loading="loading" @keydown.enter.prevent="search(true)"
            @clear="artistsNotFound = false; search(true)" :error="artistsNotFound"
            :errorMessage="'No artists found with specified condition'" :disable="loading" ref="artistNameRef">
            <template v-slot:prepend>
              <q-icon name="filter_alt" />
            </template>
            <template v-slot:append>
              <q-icon name="search" class="cursor-pointer" @click="search" />
            </template>
          </q-input>
        </div>
        <div class="col-xl-2 col-lg-2 col-md-3 col-sm-4 col-xs-4" v-if="genresValues && genresValues.length > 0">
          <q-select outlined dense v-model="genre" :options="filteredGenres" options-dense label="Genre"
            :disable="loading || route.params.genre != null" emit-value filled clearable=""
            :hint="!genre ? 'Minimum 3 characters to trigger filtering' : 'Filtering by genre'" use-input hide-selected
            input-debounce="0" @filter="onFilterGenres" @update:model-value="search(true)">
            <template v-slot:selected>
              <q-chip v-if="genre" dense square color="white" text-color="primary" class="q-my-none q-ml-xs q-mr-none">
                {{ genre.name }}
              </q-chip>
              <q-badge v-else>*none*</q-badge>
            </template>
            <template v-slot:no-option>
              <q-item>
                <q-item-section class="text-grey">
                  No results
                </q-item-section>
              </q-item>
            </template>
          </q-select>
        </div>
        <div class="col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <q-select outlined dense v-model="sortField" :options="sortFieldValues" options-dense label="Sort field"
            @update:model-value="search(true)" :disable="loading">
            <template v-slot:selected-item="scope">
              {{ scope.opt.label }}
            </template>
          </q-select>
        </div>
        <div class="col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <q-select outlined dense v-model="sortOrder" :options="sortOrderValues" options-dense label="Sort order"
            @update:model-value="search(true)" :disable="loading">
            <template v-slot:selected-item="scope">
              {{ scope.opt.label }}
            </template>
          </q-select>
        </div>
      </div>
      <div v-if="artists && artists.length > 0">
        <div class="flex flex-center" v-if="totalPages > 1">
          <q-pagination v-model="currentPageIndex" color="dark" :max="totalPages" :max-pages="5" boundary-numbers
            direction-links boundary-links @update:model-value="onPaginationChanged" :disable="loading" />
        </div>
        <div class="q-mt-md q-gutter-md row items-start">
          <router-link
            :to="{ name: artist.mbId && artist.name ? 'mbArtist' : 'artist', params: { mbid: artist.mbId, name: artist.name, tab: 'overview' } }"
            v-for="artist in artists" :key="artist.hash" v-memo="[lastChangesTimestamp]">
            <q-img img-class="artist_image" :src="artist.image || '#'" width="250px" height="250px" fit="cover">
              <div class="absolute-bottom text-subtitle1 text-center">
                {{ artist.name }}
                <p class="text-caption q-mb-none">{{ artist.totalTracks + " " + (artist.totalTracks > 1 ? 'tracks' :
                  'track') }}</p>
              </div>
              <template v-slot:loading>
                <div class="absolute-full flex flex-center bg-grey-3 text-dark">
                  <q-spinner color="pink" size="xl" />
                  <div class="absolute-bottom text-subtitle1 text-center bg-grey-5 q-py-md">
                    {{ artist.name }}
                    <p class="text-caption q-mb-none">{{ artist.totalTracks + " " + (artist.totalTracks > 1 ? 'tracks' :
                      'track') }}</p>
                  </div>
                </div>
              </template>
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
      </div>
    </q-card-section>
  </q-card>
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

import { ref, nextTick, computed, watch } from "vue";
import { useRoute, useRouter } from 'vue-router';
import { api } from 'boot/axios';
import { useQuasar } from "quasar";
import { isNumeric } from "chartist";

const $q = useQuasar();

const artistName = ref(null);
const artistsNotFound = ref(false);
const loading = ref(false);

const artists = ref([]);

const lastChangesTimestamp = ref(0);

const route = useRoute();
const router = useRouter();

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

const routeName = computed(() => route.name);
const routeParams = computed(() => route.params || {});
const routeQuery = computed(() => route.query || {});

watch(routeParams, (newValue, oldValue) => {
  if (newValue.page != oldValue.page) {
    search(false);
  } else if (routeName.value == "artists") {
    search(true);
  }
});

const sortOrder = ref(sortOrderValues[0]);

let genresValues = [];

let filteredGenres = ref([]);

const genre = ref(routeParams.value.genre || null);

const totalPages = ref(0);

//const currentPageIndex = ref(parseInt(routeParams.value.page || 1));

const currentPageIndex = computed(() => parseInt(routeParams.value.page || 1));

const artistNameRef = ref(null);

function search(resetPager) {
  artistsNotFound.value = false;
  loading.value = true;

  api.artist.search({ genre: genre.value || null, name: artistName.value || null }, resetPager ? 1: currentPageIndex.value, 32, sortField.value.value, sortOrder.value.value).then((success) => {
    artists.value = success.data.data.items;
    totalPages.value = success.data.data.pager.totalPages;
    if (success.data.data.pager.totalResults < 1) {
      artistsNotFound.value = true;
    }
    loading.value = false;
    lastChangesTimestamp.value = Date.now();
    nextTick(() => {
      artistNameRef.value.$el.focus();
    });
  }).catch((error) => {
    artists.value = [];
    $q.notify({
      type: "negative",
      message: "API Error: error loading artists",
      caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
    });
    loading.value = false;
    lastChangesTimestamp.value = Date.now();
  });
}

function onPaginationChanged(pageIndex) {
  const query = Object.assign({}, routeQuery.value);
  //OJO : https://stackoverflow.com/a/61353880
  router.push({
        name: "artistsPaged",
        params: {
          page: pageIndex
        },
        query: query
      });
      /*
  currentPageIndex.value = pageIndex;
  search(false);
  */
}

// TODO: split component
function refreshGenres() {
  loading.value = true;
  api.artistGenres.get().then((success) => {
    genresValues = success.data.genres;
    if (route.params.genre) {
      genre.value = route.params.genre;
    }
    filteredGenres.value = genresValues;
    loading.value = false;
  }).catch((error) => {
    // TODO: custom menssage
    $q.notify({
      type: "negative",
      message: "API Error: error loading genres ",
      caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
    });
    loading.value = false;
  });
}

function onFilterGenres(val, update, abort) {
  if (val.length < 3) {
    abort();
    return;
  }
  update(() => {
    const needle = val.toLowerCase();
    filteredGenres.value = genresValues.filter(genre => genre.toLowerCase().indexOf(needle) > -1);
  });
}

refreshGenres();

search(false);
</script>
