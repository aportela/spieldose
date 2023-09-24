<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <q-breadcrumbs-el icon="album" label="Browse albums" />
    </q-breadcrumbs>
    <q-card-section v-if="albums">
      <div class="row q-gutter-xs">
        <div class="col-xl-2 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <q-select outlined dense v-model="searchOn" :options="searchOnValues" options-dense label="Search on"
            @update:model-value="search(true)" :disable="loading">
            <template v-slot:selected-item="scope">
              {{ scope.opt.label }}
            </template>
          </q-select>
        </div>
        <div class="col">
          <q-input v-model="searchText" clearable type="search" outlined dense placeholder="Text condition"
            hint="Search albums with specified condition" :loading="loading" :disable="loading"
            @keydown.enter.prevent="search(true)" @clear="noAlbumsFound = false; search(true)" :error="noAlbumsFound"
            :errorMessage="'No albums found with specified condition'" ref="searchTextRef">
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
      <div class="q-pa-lg flex flex-center" v-if="totalPages > 1">
        <q-pagination v-model="currentPageIndex" color="dark" :max="totalPages" :max-pages="5" boundary-numbers
          direction-links boundary-links @update:model-value="onPaginationChanged" :disable="loading" />
      </div>
      <div class="q-gutter-md row items-start">
        <AnimatedAlbumCover v-for="album in albums" :key="album.mbId || album.title" :image="album.image"
          :title="album.title" :artistName="album.artist.name" :year="album.year" @play="onPlayAlbum(album)"
          @enqueue="onEnqueueAlbum(album)">
        </AnimatedAlbumCover>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>

import { ref, nextTick, inject } from "vue";
import { api } from "boot/axios";
import { useI18n } from "vue-i18n";
import { useQuasar } from "quasar";
import { default as AnimatedAlbumCover } from "components/AnimatedAlbumCover.vue";
import { albumActions } from "src/boot/spieldose";

const { t } = useI18n();
const $q = useQuasar();

const searchText = ref(null);

const searchOnValues = [
  {
    label: 'Title',
    value: 'title'
  },
  {
    label: 'Artist',
    value: 'albumArtistName'
  },
  {
    label: 'Title & Artist',
    value: 'all'
  }
];

const searchOn = ref(searchOnValues[2]);

const sortFieldValues = [
  {
    label: 'Title',
    value: 'title'
  },
  {
    label: 'Artist',
    value: 'albumArtistName'
  },
  {
    label: 'Year',
    value: 'year'
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
let albums = [];

const totalPages = ref(0);
const currentPageIndex = ref(1);

const searchTextRef = ref(null);

function search(resetPager) {
  if (resetPager) {
    currentPageIndex.value = 1;
  }
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
    if (searchText.value && success.data.data.pager.totalResults < 1) {
      noAlbumsFound.value = true;
    }
    nextTick(() => {
      searchTextRef.value.$el.focus();
    });
    loading.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: "API Error: error loading albums",
      caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
    });
    loading.value = false;
  });
}

function onPaginationChanged(pageIndex) {
  currentPageIndex.value = pageIndex;
  search(false);
}

function onPlayAlbum(album) {
  albumActions.play(album).then((success) => {
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
  albumActions.enqueue(album).then((success) => {
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

search(true);

</script>
