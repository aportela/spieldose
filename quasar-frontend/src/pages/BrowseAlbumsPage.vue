<template>
  <BrowserBase :disable="loading" :currentPageIndex="currentPageIndex" :totalPages="totalPages"
    :totalResults="totalResults" @paginationChanged="onPaginationChanged">
    <template #breacrumb>
      <q-breadcrumbs-el icon="person" :label="t('Browse albums')" />
    </template>
    <template #filter>
      <div class="row q-gutter-xs q-mb-md">
        <div class="col-xl-2 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <CustomSelector :disable="loading" label="Search on" :options="searchOnOptions" v-model="searchOn"
            @update:modelValue="onSearchOnChanged"></CustomSelector>
        </div>
        <div class="col">
          <CustomInputSearch :disable="loading" :loading="loading && text?.length > 0"
            hint="Search albums with specified condition" placeholder="Text condition"
            :error="warningNoItems && text?.length > 0" errorMessage="No albums found with specified condition"
            v-model="text" @submit="onTextSubmitted" ref="autoFocusRef"></CustomInputSearch>
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
      <AnimatedAlbumCover v-for="album in albums" :key="album.hash" v-memo="[lastChangesTimestamp]" :image="album.image"
          :title="album.title" :albumMbId="album.mbId" :artistMbId="album.artist.mbId" :artistName="album.artist.name"
          :year="album.year" @play="onPlayAlbum(album)" @enqueue="onEnqueueAlbum(album)">
        </AnimatedAlbumCover>
    </template>
  </BrowserBase>
</template>

<script setup>

import { ref, nextTick, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { api } from "boot/axios";
import { useQuasar } from "quasar";
import { useI18n } from "vue-i18n";
import { default as BrowserBase } from "components/BrowserBase.vue";
import { default as CustomInputSearch } from "components/CustomInputSearch.vue";
import { default as CustomSelector } from "components/CustomSelector.vue";
import { default as SortOrderSelector } from "components/SortOrderSelector.vue";
import { default as AnimatedAlbumCover } from "components/AnimatedAlbumCover.vue";
import { albumActions } from "src/boot/spieldose";


const $q = useQuasar();
const { t } = useI18n();

const route = useRoute();
const router = useRouter();

const autoFocusRef = ref(null);
const text = ref(route.query.text || null);

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

const sortField = ref(route.query.sortField == "totalTracks" ? "totalTracks" : "name");
const sortOrder = ref(route.query.sortOrder == "DESC" ? "DESC" : "ASC");
const warningNoItems = ref(false);
const loading = ref(false);
const albums = ref([]);
const lastChangesTimestamp = ref(0);

const totalPages = ref(0);
const totalResults = ref(0);
const currentPageIndex = ref(parseInt(route.query.page || 1));

router.beforeEach(async (to, from) => {
  if (from.name == "albums") {
    currentPageIndex.value = parseInt(to.query.page || 1);
    searchOn.value = to.query.searchOn || searchOnOptions[0].value;
    text.value = to.query.text || null;
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
        to.query.searchOn != from.query.searchOn ||
        to.query.text != from.query.text ||
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

function refreshURL(pageIndex, searchOn, text, sortField, sortOrder) {
  const query = Object.assign({}, route.query || {});
  query.page = pageIndex || 1;
  query.searchOn = searchOn || 'all'
  query.text = text || null;
  query.sortField = sortField || "title";
  query.sortOrder = sortOrder || "ASC";
  router.push({
    name: "albums",
    query: query
  });
}

function onPaginationChanged(pageIndex) {
  refreshURL(pageIndex, searchOn.value, text.value, sortField.value, sortOrder.value);
}

function onTextSubmitted() {
  refreshURL(1, searchOn.value, text.value, sortField.value, sortOrder.value);
}

function onSearchOnChanged(searchOn) {
  refreshURL(1, searchOn, text.value, sortField.value, sortOrder.value);
}

function onSortFieldChanged(sortField) {
  refreshURL(currentPageIndex.value, searchOn.value, text.value, sortField, sortOrder.value);
}

function onSortOrderChanged(sortOrder) {
  refreshURL(currentPageIndex.value, searchOn.value, text.value, sortField.value, sortOrder);
}

function browse() {
  warningNoItems.value = false;
  loading.value = true;
  const filter = {
    title: searchOn.value == 'title' ? text.value : null,
    albumArtistName: searchOn.value == 'albumArtistName' ? text.value : null,
    text: searchOn.value == 'all' ? text.value : null,
  };
  api.album.search(filter, currentPageIndex.value, 32, sortField.value, sortOrder.value).then((success) => {
    albums.value = success.data.data.items.map((item) => {
      item.image = item.covers.small;
      return (item);
    });
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
    albums.value = [];
    totalPages.value = 0;
    totalResults.value = 0;
    $q.notify({
      type: "negative",
      message: t("API Error: error loading albums"),
      caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
    });
    loading.value = false;
    lastChangesTimestamp.value = Date.now();
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
  browse();
});

</script>
