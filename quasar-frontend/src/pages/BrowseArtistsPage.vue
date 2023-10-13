<template>
  <BrowserBase :disable="loading" :currentPageIndex="currentPageIndex" :totalPages="totalPages"
    :totalResults="totalResults" @paginationChanged="onPaginationChanged">
    <template #breacrumb>
      <q-breadcrumbs-el icon="person" :label="t('Browse artists')" />
    </template>
    <template #filter>
      <div class="row q-gutter-xs q-mb-md">
        <div class="col">
          <CustomInputSearch :disable="loading" :loading="loading && name?.length > 0" hint="Search by artist name" placeholder="Text condition"
            :error="warningNoItems && name?.length > 0" errorMessage="No artists found with the specified condition filter" v-model="name"
            @submit="onNameSubmitted" ref="autoFocusRef"></CustomInputSearch>
        </div>
        <div class="col-xl-2 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <ArtistsGenreSelector :disable="loading" :defaultGenre="genre" @change="onGenreChanged">
          </ArtistsGenreSelector>
        </div>
        <div class="col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <CustomSelector :disable="loading" label="Sort field" :options="sortFieldOptions" v-model="sortField"
            @update:modelValue="onSortFieldChanged"></CustomSelector>
        </div>
        <div class="col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <SortOrderSelector :disable="loading" v-model="sortOrder" @update:modelValue="onSortOrderChanged">
          </SortOrderSelector>
        </div>
      </div>
    </template>
    <template #items>
      <router-link :to="{ name: 'artist', params: { name: artist.name }, query: { mbid: artist.mbId, tab: 'overview' } }"
        v-for="artist in artists" :key="artist.hash" v-memo="[lastChangesTimestamp]">
        <q-img img-class="sp-artist-image-filter" :src="artist.image || '#'" width="250px" height="250px" fit="cover">
          <div class="absolute-bottom text-subtitle1 text-center">
            {{ artist.name }}
            <p class="text-caption q-mb-none">{{ artist.totalTracks }} {{ t(artist.totalTracks > 1 ? "tracks" : "track")
            }}
            </p>
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
    </template>
  </BrowserBase>
</template>

<style>
img.sp-artist-image-filter {
  -webkit-filter: grayscale(100%) blur(4px) opacity(0.5);
  /* Safari 6.0 - 9.0 */
  filter: grayscale(100%) blur(4px) opacity(0.5);
  transition: filter 0.2s ease-in;
}

img.sp-artist-image-filter:hover {
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
import { default as BrowserBase } from "components/BrowserBase.vue";
import { default as ArtistsGenreSelector } from "components/ArtistsGenreSelector.vue";
import { default as CustomInputSearch } from "components/CustomInputSearch.vue";
import { default as CustomSelector } from "components/CustomSelector.vue";
import { default as SortOrderSelector } from "components/SortOrderSelector.vue";

const $q = useQuasar();
const { t } = useI18n();

const route = useRoute();
const router = useRouter();

const autoFocusRef = ref(null);
const name = ref(route.query.name || null);
const genre = ref(route.query.genre || null);
const sortFieldOptions = [
  {
    label: "Sort by artist name",
    value: "name"
  },
  {
    label: "Sort by total tracks",
    value: "totalTracks"
  }
];

const sortField = ref(sortFieldOptions[0].value);
const sortOrder = ref(route.query.sortOrder == "DESC" ? "DESC" : "ASC");
const warningNoItems = ref(false);
const loading = ref(false);
const artists = ref([]);
const lastChangesTimestamp = ref(0);

const totalPages = ref(0);
const totalResults = ref(0);
const currentPageIndex = ref(parseInt(route.query.page || 1));

router.beforeEach(async (to, from) => {
  if (from.name == "artists") {
    currentPageIndex.value = parseInt(to.query.page || 1);
    genre.value = to.query.genre || null;
    name.value = to.query.name || null;
    sortOrder.value = to.query.sortOrder == "DESC" ? "DESC" : "ASC";
    //sortField.value = sortFieldOptions[to.query.sortField == "totalTracks" ? 1 : 0].value;
    if (
      (to.name == "artists") &&
      (
        to.query.page != from.query.page ||
        to.query.name != from.query.name ||
        to.query.genre != from.query.genre ||
        to.query.sortOrder != from.query.sortOrder ||
        to.query.sortField != from.query.sortField
      )
    ) {
      nextTick(() => {
        browse();
      });
    }
  }
});

function refreshURL(pageIndex, name, genre, sortField, sortOrder) {
  const query = Object.assign({}, route.query || {});
  query.page = pageIndex || 1;
  query.name = name || null;
  query.genre = genre || null;
  query.sortField = sortField || "name";
  query.sortOrder = sortOrder || "ASC";
  router.push({
    name: "artists",
    query: query
  });
}

function onPaginationChanged(pageIndex) {
  refreshURL(pageIndex, name.value, genre.value, sortField.value, sortOrder.value);
}

function onNameSubmitted() {
  refreshURL(1, name.value, genre.value, sortField.value, sortOrder.value);
}

function onGenreChanged(genre) {
  refreshURL(1, name.value, genre, sortField.value, sortOrder.value);
}

function onSortFieldChanged(sortField) {
  refreshURL(currentPageIndex.value, name.value, genre.value, sortField, sortOrder.value);
}

function onSortOrderChanged(sortOrder) {
  refreshURL(currentPageIndex.value, name.value, genre.value, sortField.value, sortOrder);
}

function browse() {
  warningNoItems.value = false;
  loading.value = true;
  api.artist.search({ genre: genre.value || null, name: name.value || null }, currentPageIndex.value, 32, sortField.value, sortOrder.value).then((success) => {
    artists.value = success.data.data.items;
    totalPages.value = success.data.data.pager.totalPages;
    totalResults.value = success.data.data.pager.totalResults;
    warningNoItems.value = success.data.data.pager.totalResults < 1;
    loading.value = false;
    lastChangesTimestamp.value = Date.now();
    nextTick(() => {
      if (autoFocusRef.value) {
        autoFocusRef.value.focus();
      }
    });
  }).catch((error) => {
    artists.value = [];
    totalPages.value = 0;
    totalResults.value = 0;
    $q.notify({
      type: "negative",
      message: t("API Error: error loading artists"),
      caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
    });
    loading.value = false;
    lastChangesTimestamp.value = Date.now();
  });
}

onMounted(() => {
  browse();
});

</script>
