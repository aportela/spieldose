<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <q-breadcrumbs-el icon="album" label="Browse albums" />
    </q-breadcrumbs>
    <q-card-section v-if="albums">
      <div class="row q-gutter-xs">
        <div class="col-xl-2 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <CustomSelector :disable="loading" label="Search on" :options="searchOnOptions" v-model="searchOn" @update:modelValue="onSortFieldChanged"></CustomSelector>
        </div>
        <div class="col">
          <q-input v-model="searchText" clearable type="search" outlined dense placeholder="Text condition"
            hint="Search albums with specified condition" :loading="loading && searchText?.length > 0" :disable="loading"
            @keydown.enter.prevent="onTextChanged" @clear="search(true)" :error="noAlbumsFound"
            :errorMessage="'No albums found with specified condition'" ref="autoFocusRef">
            <template v-slot:prepend>
              <q-icon name="filter_alt" />
            </template>
            <template v-slot:append>
              <q-icon name="search" class="cursor-pointer" @click="search" />
            </template>
          </q-input>
        </div>
        <div class="col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <CustomSelector :disable="loading" label="Sort field" :options="sortFieldOptions" v-model="sortField" @update:modelValue="onSortFieldChanged"></CustomSelector>
        </div>
        <div class="col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <SortOrderSelector :disable="loading" v-model="sortOrder" @update:modelValue="onSortOrderChanged"></SortOrderSelector>
        </div>
      </div>
      <div class="q-pa-lg flex flex-center" v-if="totalPages > 1">
        <q-pagination v-model="currentPageIndex" color="dark" :max="totalPages" :max-pages="5" boundary-numbers
          direction-links boundary-links @update:model-value="onPaginationChanged" :disable="loading" />
      </div>
      <div class="q-gutter-md row items-start">
        <AnimatedAlbumCover v-for="album in albums" :key="album.mbId || album.title" :image="album.image"
          :title="album.title" :albumMbId="album.mbId" :artistMbId="album.artist.mbId" :artistName="album.artist.name"
          :year="album.year" @play="onPlayAlbum(album)" @enqueue="onEnqueueAlbum(album)">
        </AnimatedAlbumCover>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>

import { ref, nextTick, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { api } from "boot/axios";
import { useI18n } from "vue-i18n";
import { useQuasar } from "quasar";
import { default as CustomSelector } from "components/CustomSelector.vue";
import { default as SortOrderSelector } from "components/SortOrderSelector.vue";
import { default as AnimatedAlbumCover } from "components/AnimatedAlbumCover.vue";
import { albumActions } from "src/boot/spieldose";


const $q = useQuasar();
const { t } = useI18n();

const route = useRoute();
const router = useRouter();

const textRef = ref(null);
const searchText = ref(route.query.q || null);

const searchOnOptions = [
  {
    label: "Title",
    value: "title"
  },
  {
    label: "Artist",
    value: "albumArtistName"
  },
  {
    label: "Title & Artist",
    value: "all"
  }
];

const searchOn = ref(searchOnOptions[0].value);

const sortFieldOptions = [
  {
    label: "Title",
    value: "title"
  },
  {
    label: "Artist",
    value: "albumArtistName"
  },
  {
    label: "Year",
    value: "year"
  }
];

const sortField = ref(sortFieldOptions[0].value);
const sortOrder = ref(route.query.sortOrder == "DESC" ? "DESC" : "ASC");

const noAlbumsFound = ref(false);
const loading = ref(false);
let albums = [];

const totalPages = ref(0);
const totalResults = ref(0);
const currentPageIndex = ref(parseInt(route.query.page || 1));

const autoFocusRef = ref(null);

router.beforeEach(async (to, from) => {
  if (from.name == "albums") {
    currentPageIndex.value = parseInt(to.query.page || 1);
    searchOn.value = to.query.searchOn || searchOnOptions[0].value;
    searchText.value = to.query.q || null;
    sortOrder.value = to.query.sortOrder == "DESC" ? "DESC" : "ASC";
    switch (to.query.sortField) {
      case 'title':
        sortField.value = sortFieldOptions[0].value
        break;
      case 'albumArtistName':
        sortField.value = sortFieldOptions[1].value
        break;
      case 'year':
        sortField.value = sortFieldOptions[2].value
        break;
      default:
        sortField.value = sortFieldOptions[0].value
        break;
    }
    if (
      (to.name == "albums") &&
      (
        to.query.page != from.query.page ||
        to.query != from.query ||
        to.searchOn != from.searchOn ||
        to.query.sortOrder != from.query.sortOrder ||
        to.query.sortField != from.query.sortField
      )
    ) {
      nextTick(() => {
        search();
      });
    }
  }
});

function refreshURL(pageIndex, name, searchOn, sortField, sortOrder) {
  const query = Object.assign({}, route.query || {});
  query.page = pageIndex || 1;
  query.q = name || null;
  query.searchOn = searchOn || 'all'
  query.sortField = sortField || "title";
  query.sortOrder = sortOrder || "ASC";
  router.push({
    name: "albums",
    query: query
  });
}

function onPaginationChanged(pageIndex) {
  refreshURL(pageIndex, searchText.value, searchOn.value, sortField.value, sortOrder.value);
}

function onTextChanged() {
  refreshURL(1, searchText.value, searchOn.value, sortField.value, sortOrder.value);
}

function onSearchOnChanged() {
  refreshURL(1, searchText.value, searchOn.value, sortField.value, sortOrder.value);
}

function onSortFieldChanged(selectedSortField) {
  refreshURL(currentPageIndex.value, searchOn.value, searchText.value, selectedSortField, sortOrder.value);
}

function onSortOrderChanged(selectedSortOrder) {
  refreshURL(currentPageIndex.value, searchOn.value, searchText.value, sortField.value, selectedSortOrder);
}

function search() {
  noAlbumsFound.value = false;
  loading.value = true;
  let filter = {
    title: searchOn.value.value == 'title' ? searchText.value : null,
    albumArtistName: searchOn.value.value == 'albumArtistName' ? searchText.value : null,
    text: searchOn.value.value == 'all' ? searchText.value : null,
  };
  api.album.search(filter, currentPageIndex.value, 32, sortField.value.value, sortOrder.value.value).then((success) => {
    albums = success.data.data.items.map((item) => {
      item.image = item.covers.small;
      return (item);
    });
    totalPages.value = success.data.data.pager.totalPages;
    totalResults.value = success.data.data.pager.totalResults;
    if (searchText.value && success.data.data.pager.totalResults < 1) {
      noAlbumsFound.value = true;
    }
    loading.value = false;
    nextTick(() => {
      autoFocusRef.value.$el.focus();
    });
  }).catch((error) => {
    albums = [];
    totalPages.value = 0;
    totalResults.value = 0;
    $q.notify({
      type: "negative",
      message: "API Error: error loading albums",
      caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
    });
    loading.value = false;
  });
}

function onPlayAlbum(album) {
  albumActions.play(
    album.mbId || null,
    album.title || null,
    album.artist ? album.artist.mbId : null,
    album.artist ? album.artist.name : null,
    album.year || null
  ).then((success) => {
  })
    .catch((error) => {
      switch (error.response.status) {
        default:
          // TODO: custom message
          $q.notify({
            type: "negative",
            message: t("API Error: error playing album"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
    });
}

function onEnqueueAlbum(album) {
  albumActions.enqueue(album.mbId || null,
    album.title || null,
    album.artist ? album.artist.mbId : null,
    album.artist ? album.artist.name : null,
    album.year || null
  ).then((success) => {
  })
    .catch((error) => {
      switch (error.response.status) {
        default:
          // TODO: custom message
          $q.notify({
            type: "negative",
            message: t("API Error: error enqueueing album"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
    });
}

onMounted(() => {
  search();
});

</script>
