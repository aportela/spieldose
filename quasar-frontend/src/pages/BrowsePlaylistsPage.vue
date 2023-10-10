<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <q-breadcrumbs-el icon="list" label="Browse playlists" />
    </q-breadcrumbs>
    <q-card-section v-if="playlists">
      <div class="row q-gutter-xs">
        <div class="col">
          <q-input v-model="playlistName" clearable type="search" outlined dense placeholder="Text condition"
            hint="Search playlists with name" :loading="loading" :disable="loading" @keydown.enter.prevent="search(true)"
            @clear="noPlaylistsFound = false; search(true)" :error="noPlaylistsFound"
            :errorMessage="'No playlists found with specified condition'">
            <template v-slot:prepend>
              <q-icon name="filter_alt" />
            </template>
            <template v-slot:append>
              <q-icon name="search" class="cursor-pointer" @click="search" />
            </template>
          </q-input>
        </div>
        <div class="col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <q-select outlined dense v-model="playlistType" :options="playlistTypeValues" options-dense label="Type"
            @update:model-value="search(true)" :disable="loading || filterByOwnerId != null">
            <template v-slot:selected-item="scope">
              {{ scope.opt.label }}
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
        <div class="col-xl-2 col-lg-2 col-md-3 col-sm-4 col-xs-4">
          <q-select outlined dense v-model="style" :options="styleValues" options-dense label="Style" :disable="loading">
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
      <div class="q-gutter-sm row items-start">
        <BrowsePlaylistItem v-for="playlist in playlists" :key="playlist.id" :playlist="playlist" :mode="style.value"
          @play="onPlay(playlist.id)" @delete="onDelete(playlist.id)">
        </BrowsePlaylistItem>
      </div>
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
    </q-card-section>
  </q-card>
</template>

<script setup>

import { ref, watch, inject } from "vue";
import { api } from 'boot/axios'
import { useQuasar } from "quasar";
import { useI18n } from 'vue-i18n';

import { useRoute } from 'vue-router';
import { default as BrowsePlaylistItem } from "components/BrowsePlaylistItem.vue"
import { useSpieldoseStore } from "stores/spieldose";
import { playListActions } from "boot/spieldose";

const $q = useQuasar();
const { t } = useI18n();

const spieldoseStore = useSpieldoseStore();

const playlistName = ref(null);
const noPlaylistsFound = ref(false);
const loading = ref(false);
let playlists = [];

const route = useRoute();

const filterByOwnerId = ref(route.name == "playlistsByUserId" && route.params.id ? route.params.id : null);

const totalPages = ref(0);
const currentPageIndex = ref(1);

const playlistTypeValues = [
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

const playlistType = ref(!filterByOwnerId.value ? playlistTypeValues[0] : playlistTypeValues[2]);

const sortFieldValues = [
  {
    label: 'Name',
    value: 'name'
  },
  {
    label: 'Last update',
    value: 'updated'
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

const styleValues = [
  {
    label: 'Mosaic',
    value: 'mosaic'
  },
  {
    label: 'Vinyl collection',
    value: 'vinyls'
  }
];

const style = ref(styleValues[0]);


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

function search(resetPager) {
  if (resetPager) {
    currentPageIndex.value = 1;
  }
  noPlaylistsFound.value = false;
  loading.value = true;
  api.playlist.search({ name: playlistName.value }, currentPageIndex.value, 32, sortField.value.value, sortOrder.value.value).then((success) => {
    playlists = success.data.data.items;
    totalPages.value = success.data.data.pager.totalPages;
    if (playlistName.value && success.data.data.pager.totalResults < 1) {
      noPlaylistsFound.value = true;
    }
    loading.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: "API Error: error loading playlists",
      caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
    });
    loading.value = false;
  });
}

function onPaginationChanged(pageIndex) {
  currentPageIndex.value = pageIndex;
  search(false);
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
    $q.notify({
      type: "positive",
      message: "Playlist deleted",
    });
    loading.value = false;
    search(true);
  }).catch((error) => {
    // TODO
    loading.value = false;
  });

}

search(true);

</script>
