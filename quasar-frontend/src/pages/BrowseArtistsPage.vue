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
            :hint="t('Search by artist name')" :loading="loading" @keydown.enter.prevent="onChangeName" @clear="search"
            :error="noItemsFound" :errorMessage="t('No results found with the specified condition filter')"
            :disable="loading" ref="artistNameRef">
            <template v-slot:prepend>
              <q-icon name="filter_alt" />
            </template>
            <template v-slot:append>
              <q-icon name="search" class="cursor-pointer" @click="onChangeName" />
            </template>
          </q-input>
        </div>
        <div class="col-xl-2 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <ArtistsGenreSelector :defaultGenre="filterByGenre" @change="onChangeGenre"></ArtistsGenreSelector>
        </div>
        <div class="col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <SortFieldSelector :options="sortFieldOptions" :field="sortField" @change="onChangeSortField">
          </SortFieldSelector>
        </div>
        <div class="col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <SortOrderSelector :order="sortOrder" @change="onChangeSortOrder"></SortOrderSelector>
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

import { ref, nextTick, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { api } from "boot/axios";
import { useQuasar } from "quasar";
import { useI18n } from "vue-i18n";
import { default as ArtistsGenreSelector } from "components/ArtistsGenreSelector.vue";
import { default as SortFieldSelector } from "components/SortFieldSelector.vue";
import { default as SortOrderSelector } from "components/SortOrderSelector.vue";

const { t } = useI18n();
const $q = useQuasar();

const route = useRoute();
const router = useRouter();

const artistNameRef = ref(null);
const artistName = ref(route.query.q || null);
const filterByGenre = ref(route.query.genre || null);
const sortFieldOptions = [
  {
    label: 'Sort by artist name',
    value: 'name'
  },
  {
    label: 'Sort by total tracks',
    value: 'totalTracks'
  }
];
const sortField = ref(sortFieldOptions[0].value);
const sortOrder = ref(null);
const noItemsFound = ref(false);
const loading = ref(false);
const artists = ref([]);
const lastChangesTimestamp = ref(0);

const totalPages = ref(0);
const currentPageIndex = ref(parseInt(route.params.page || 1));

router.beforeEach(async (to, from) => {
  if (from.name == "artists" || from.name == "artistsPaged") {
    currentPageIndex.value = parseInt(to.params.page || 1);
    filterByGenre.value = to.query.genre || null;
    artistName.value = to.query.q || null;
    sortOrder.value = to.query.sortOrder == "DESC" ? to.query.sortOrder : "ASC";
    sortField.value = sortFieldOptions[to.query.sortField == "totalTracks" ? 1 : 0].value;
    if ((to.name == "artists" || to.name == "artistsPaged") && (to.params.page != from.params.page || to.query != from.query)) {
      nextTick(() => {
        search();
      });
    }
  }
});

function refreshURL(pageIndex, artistName, selectedGenre, sortField, sortOrder) {
  const query = Object.assign({}, route.query || {});
  query.q = artistName || null;
  query.genre = selectedGenre || null;
  query.sortField = sortField || "name";
  query.sortOrder = sortOrder || "ASC";
  router.push({
    name: "artistsPaged",
    params: {
      page: pageIndex
    },
    query: query
  });
}

function onPaginationChanged(pageIndex) {
  refreshURL(pageIndex, artistName.value, filterByGenre.value, sortField.value, sortOrder.value);
}

function onChangeName() {
  refreshURL(1, artistName.value, filterByGenre.value, sortField.value, sortOrder.value);
}

function onChangeGenre(selectedGenre) {
  refreshURL(1, artistName.value, selectedGenre, sortField.value, sortOrder.value);
}

function onChangeSortField(selectedSortField) {
  refreshURL(currentPageIndex.value, artistName.value, filterByGenre.value, selectedSortField, sortOrder.value);
}

function onChangeSortOrder(selectedSortOrder) {
  refreshURL(currentPageIndex.value, artistName.value, filterByGenre.value, sortField.value, selectedSortOrder);
}

function search() {
  noItemsFound.value = false;
  loading.value = true;
  api.artist.search({ genre: filterByGenre.value || null, name: artistName.value || null }, currentPageIndex.value, 32, sortField.value, sortOrder.value).then((success) => {
    artists.value = success.data.data.items;
    totalPages.value = success.data.data.pager.totalPages;
    noItemsFound.value = success.data.data.pager.totalResults < 1;
    loading.value = false;
    lastChangesTimestamp.value = Date.now();
    nextTick(() => {
      artistNameRef.value.focus();
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

onMounted(() => {
  search();
});

</script>
