<template>
  <BrowserBase :disable="loading" :currentPageIndex="currentPageIndex" :totalPages="totalPages"
    :totalResults="totalResults" @paginationChanged="onPaginationChanged">
    <template #breacrumb>
      <q-breadcrumbs-el icon="person" :label="t('Browse playlists')" />
    </template>
    <template #filter>
      <div class="row q-gutter-xs q-mb-md">
        <div class="col">
          <CustomInputSearch :disable="loading" :loading="loading && name?.length > 0"
            hint="Search playlists with specified condition" placeholder="Text condition"
            :error="warningNoItems && name?.length > 0" errorMessage="No playlists found with specified condition"
            v-model="name" @submit="onNameChanged" @clear="onNameChanged" ref="autoFocusRef"></CustomInputSearch>
        </div>
        <div class="col-xl-2 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <CustomSelector :disable="loading" label="Playlist type" :options="typeOptions" v-model="type"
            @update:modelValue="onTypeChanged"></CustomSelector>
        </div>
        <div class="col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <CustomSelector :disable="loading" label="Sort field" :options="sortFieldOptions" v-model="sortField"
            @update:modelValue="onSortFieldChanged"></CustomSelector>
        </div>
        <div class="col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <SortOrderSelector :disable="loading" v-model="sortOrder" @update:modelValue="onSortOrderChanged">
          </SortOrderSelector>
        </div>
        <div class="col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <CustomSelector :disable="loading" label="Playlist style" :options="styleOptions" v-model="style"></CustomSelector>
        </div>
      </div>
    </template>
    <template #items>
      <BrowsePlaylistItem v-for="playlist in playlists" :key="playlist.id" :playlist="playlist" :mode="style"
        @play="onPlay(playlist.id)" @delete="onDelete(playlist.id)">
      </BrowsePlaylistItem>
    </template>
  </BrowserBase>
  <q-dialog v-model="showDeleteConfirmationDialog" persistent>
    <q-card>
      <q-card-section class="row items-center">
        <q-avatar icon="info" />
        <span class="q-ml-sm">Delete playlist</span>
      </q-card-section>
      <q-card-section>
        <p>Are you sure ?</p>
      </q-card-section>
      <q-card-actions align="right">
        <q-btn flat label="Cancel" color="primary" v-close-popup />
        <q-btn flat label="YES" color="primary" @click="onDeletePlaylist" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>

import { ref, nextTick, onMounted, inject } from "vue";
import { useRoute, useRouter } from "vue-router";
import { api } from "boot/axios";
import { useQuasar } from "quasar";
import { useI18n } from "vue-i18n";
import { default as BrowserBase } from "components/BrowserBase.vue";
import { default as CustomInputSearch } from "components/CustomInputSearch.vue";
import { default as CustomSelector } from "components/CustomSelector.vue";
import { default as SortOrderSelector } from "components/SortOrderSelector.vue";
import { default as BrowsePlaylistItem } from "components/BrowsePlaylistItem.vue"
import { playListActions } from "boot/spieldose";
import { useSpieldoseStore } from "stores/spieldose";
import { spieldoseEventNames } from "boot/events";

const $q = useQuasar();
const { t } = useI18n();

const route = useRoute();
const router = useRouter();

const spieldoseStore = useSpieldoseStore();

const autoFocusRef = ref(null);
const name = ref(route.query.name || null);

const typeOptions = [
  {
    label: 'All playlists',
    value: 'all',
    disable: false
  },
  {
    label: 'My playlists',
    value: 'mine',
    disable: false
  },
  {
    label: 'User playlists',
    value: 'userPlaylists',
    disable: true
  },
];

const sortFieldOptions = [
  {
    label: 'Name',
    value: 'name'
  },
  {
    label: 'Last update',
    value: 'updated'
  }
];

const styleOptions = [
  {
    label: 'Mosaic',
    value: 'mosaic'
  },
  {
    label: 'Vinyls',
    value: 'vinyls'
  }
];

const type = ref(!true ? typeOptions[0].value : typeOptions[2].value);
const sortField = ref(route.query.sortField == "updated" ? "updated" : "name");
const sortOrder = ref(route.query.sortOrder == "DESC" ? "DESC" : "ASC");
const style = ref(route.query.style == "vinyls" ? "vinyls": "mosaic");
const warningNoItems = ref(false);
const loading = ref(false);
const playlists = ref([]);
const lastChangesTimestamp = ref(0);

const totalPages = ref(0);
const totalResults = ref(0);
const currentPageIndex = ref(parseInt(route.query.page || 1));

router.beforeEach(async (to, from) => {
  if (from.name == "playlists") {
    currentPageIndex.value = parseInt(to.query.page || 1);
    type.value = to.query.type || typeOptions[0].value;
    name.value = to.query.name || null;
    sortOrder.value = to.query.sortOrder == "DESC" ? "DESC" : "ASC";
    sortField.value = sortFieldOptions[to.query.sortField == "updated" ? 1 : 0].value;
    style.value = styleOptions[to.query.style == "vinyls" ? 1 : 0].value;
    if (
      (to.name == "playlists") &&
      (
        to.query.page != from.query.page ||
        to.query.type != from.query.type ||
        to.query.name != from.query.name ||
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

function refreshURL(pageIndex, type, name, sortField, sortOrder) {
  const query = Object.assign({}, route.query || {});
  query.page = pageIndex || 1;
  query.type = type || 'all'
  query.name = name || null;
  query.sortField = sortField || "title";
  query.sortOrder = sortOrder || "ASC";
  query.style = styleOptions[style.value == "vinyls" ? 1 : 0].value
  router.push({
    name: "playlists",
    query: query
  });
}

function onPaginationChanged(pageIndex) {
  refreshURL(pageIndex, type.value, name.value, sortField.value, sortOrder.value);
}

function onNameChanged() {
  refreshURL(1, type.value, name.value, sortField.value, sortOrder.value);
}

function onTypeChanged(type) {
  refreshURL(1, type, name.value, sortField.value, sortOrder.value);
}

function onSortFieldChanged(sortField) {
  refreshURL(currentPageIndex.value, type.value, name.value, sortField, sortOrder.value);
}

function onSortOrderChanged(sortOrder) {
  refreshURL(currentPageIndex.value, type.value, name.value, sortField.value, sortOrder);
}

const filterByOwnerId = ref(route.name == "playlistsByUserId" && route.params.id ? route.params.id : null);




const showDeleteConfirmationDialog = ref(false);
const selectedPlaylistId = ref(null);

// https://stackoverflow.com/a/1484514
function getRandomColor() {
  const allowed = "ABCDEF0123456789";
  let S = "#";
  while (S.length < 7) {
    S += allowed.charAt(Math.floor((Math.random() * 16) + 1));
  }
  return (S);
}

function browse() {
  warningNoItems.value = false;
  loading.value = true;
  loading.value = true;
  api.playlist.search({ name: name.value }, currentPageIndex.value, 32, sortField.value.value, sortOrder.value.value).then((success) => {
    playlists.value = success.data.data.items;
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
    playlists.value = [];
    totalPages.value = 0;
    totalResults.value = 0;
    $q.notify({
      type: "negative",
      message: "API Error: error loading playlists",
      caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
    });
    loading.value = false;
    lastChangesTimestamp.value = Date.now();
  });
}


function onPlay(playlistId) {
  spieldoseStore.interact();
  playListActions.play(playlistId)
    .then((success) => { }).catch((error) => {
      $q.notify({
        type: "negative",
        message: t("API Error: error loading playlist"),
        caption: t("API Error: fatal error details", {
          status: error.response.status,
          statusText: error.response.statusText,
        }),
      });
    });
}

function onDelete(playlistId) {
  showDeleteConfirmationDialog.value = true;
  selectedPlaylistId.value = playlistId;
}

function onDeletePlaylist() {
  loading.value = true;
  api.playlist.delete(selectedPlaylistId.value).then((success) => {
    loading.value = false;
    showDeleteConfirmationDialog.value = false;
    selectedPlaylistId.value = null;
    spieldoseStore.data.currentPlaylist.playlist = null;
    $q.notify({
      type: "positive",
      message: "Playlist deleted",
    });
    loading.value = false;
    browse(true);
  }).catch((error) => {
    // TODO
    loading.value = false;
  });

}

onMounted(() => {
  browse();
});

const bus = inject('bus');

bus.on(spieldoseEventNames.track.setFavorite, (data) => {
  browse(true);
});

bus.on(spieldoseEventNames.track.unSetFavorite, (data) => {
  browse(true);
});
</script>
