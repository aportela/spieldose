<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="search" label="Search" />
      </q-breadcrumbs>
      <q-card>
        <q-tabs v-model="tab" dense class2="text-grey" active-color2="primary" indicator-color2="primary" align="justify"
          narrow-indicator>
          <q-tab name="global" label="Global search" />
          <q-tab name="advanced" label="Advanced search" />
        </q-tabs>
        <q-separator />
        <q-tab-panels v-model="tab" animated>
          <q-tab-panel name="global">
            <form @submit.prevent.stop="onSearch" autocorrect="off" autocapitalize="off" autocomplete="off"
              spellcheck="false">
              <q-input clearable clear-icon="close" dense outlined type="text" name="searchText"
                label="Search text on all fields..." v-model="searchText" @key:model-value="onSearch(true)"
                :disable="loading" ref="inputSearchTextRef">
                <template v-slot:prepend>
                  <q-icon name="filter_alt" />
                </template>
                <template v-slot:after>
                  <q-btn type="submit" label="launch search" outline icon="search" :loading="loading" :disable="loading || ! searchText" class="q-pa-sm"
                    @click="onSearch"></q-btn>
                </template>
              </q-input>
            </form>
          </q-tab-panel>
          <q-tab-panel name="advanced">
            <form>
              <q-input outlined v-model="text" label="Track title" placeholder="type text condition"
                hint="Search on track title" dense clearable clear-icon="close" :disable="loading" />
              <q-input outlined v-model="text" label="Artist name" placeholder="type text condition"
                hint="Search on artist name" dense clearable clear-icon="close" :disable="loading" />
              <q-input outlined v-model="text" label="Album name" placeholder="type text condition"
                hint="Search on album name" dense clearable clear-icon="close" :disable="loading" />
              <q-input outlined v-model="text" label="Album artist" placeholder="type text condition"
                hint="Search on album artist" dense clearable clear-icon="close" :disable="loading" />
              <q-input outlined v-model="text" label="Year" placeholder="type year condition" hint="Search on track year"
                dense clearable clear-icon="close" :disable="loading" />
            </form>
          </q-tab-panel>
        </q-tab-panels>
      </q-card>
      <div v-if="searchResults && searchResults.length > 0">
        <div class="q-pa-lg flex flex-center" v-if="totalPages > 1">
          <q-pagination v-model="currentPageIndex" color="dark" :max="totalPages" :max-pages="5" boundary-numbers
            direction-links boundary-links @update:model-value="onPaginationChanged" :disable="loading" />
        </div>
        <q-markup-table flat bordered>
          <caption class="q-pa-sm bg-grey-2">
            <q-btn outline @click="onSendPlaylist" :disable="!allowSendToPlayList" class="full-width">Send results to
              playlist</q-btn>
          </caption>
          <thead>
            <tr class="bg-grey-2 text-grey-10">
              <th class="text-left cursor-pointer" @click="onSortBy('title')">Title <q-icon size="xl"
                  :name="sortOrder.value == 'DESC' ? 'arrow_drop_down' : 'arrow_drop_up'"
                  v-if="sortField == 'title'"></q-icon></th>
              <th class="text-left cursor-pointer" @click="onSortBy('artistName')">Artist <q-icon size="xl"
                  :name="sortOrder.value == 'DESC' ? 'arrow_drop_down' : 'arrow_drop_up'"
                  v-if="sortField == 'artistName'"></q-icon></th>
              <th class="text-left cursor-pointer" @click="onSortBy('albumArtistName')">Album Artist <q-icon size="xl"
                  :name="sortOrder.value == 'DESC' ? 'arrow_drop_down' : 'arrow_drop_up'"
                  v-if="sortField == 'albumArtistName'"></q-icon></th>
              <th class="text-left cursor-pointer" @click="onSortBy('releaseTitle')">Album <q-icon size="xl"
                  :name="sortOrder.value == 'DESC' ? 'arrow_drop_down' : 'arrow_drop_up'"
                  v-if="sortField == 'releaseTitle'"></q-icon></th>
              <th class="text-right cursor-pointer" @click="onSortBy('trackNumber')"><q-icon size="xl"
                  :name="sortOrder.value == 'DESC' ? 'arrow_drop_down' : 'arrow_drop_up'"
                  v-if="sortField == 'trackNumber'"></q-icon> Album Track nº</th>
              <th class="text-right cursor-pointer" @click="onSortBy('year')"><q-icon size="xl"
                  :name="sortOrder.value == 'DESC' ? 'arrow_drop_down' : 'arrow_drop_up'"
                  v-if="sortField == 'year'"></q-icon> Year</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="searchResult in searchResults" :key="searchResult.track.id" class="non-selectable">
              <td class="text-left">{{ searchResult.track.title }}</td>
              <td class="text-left"><router-link v-if="searchResult.track.artist && searchResult.track.artist.name"
                  :to="{ name: 'artist', params: { name: searchResult.track.artist.name } }"><q-icon name="link"
                    class="q-mr-sm"></q-icon>{{
                      searchResult.track.artist.name }}</router-link></td>
              <td class="text-left"><router-link v-if="searchResult.track.album && searchResult.track.album.artist.name"
                  :to="{ name: 'artist', params: { name: searchResult.track.album.artist.name } }"><q-icon name="link"
                    class="q-mr-sm"></q-icon>{{ searchResult.track.album.artist.name }}</router-link></td>
              <td class="text-left">{{ searchResult.track.album ? searchResult.track.album.title : null }}<span
                  class="is-clickable"><i class="fas fa-link ml-1"></i></span></td>
              <td class="text-right">{{ searchResult.track.trackNumber }}</td>
              <td class="text-right">{{ searchResult.track.album ? searchResult.track.album.year : null }}</td>
              <td class="text-center">
                <q-btn-group outline>
                  <q-btn size="sm" color="white" text-color="grey-5" icon="play_arrow" title="Play" :disable="loading"
                    @click="onPlayTrack(searchResult)" />
                  <q-btn size="sm" color="white" text-color="grey-5" icon="download" title="Download" :disable="loading"
                    :href="searchResult.track.url" />
                  <q-btn size="sm" color="white" :text-color="searchResult.track.favorited ? 'pink': 'grey-5'" icon="favorite" title="Toggle favorite" :disable="loading" @click="onToggleFavorite(searchResult.track)" />
                </q-btn-group>
              </td>
            </tr>
          </tbody>
          <q-inner-loading :showing="loading">
            <q-spinner-gears size="50px" color="pink" />
          </q-inner-loading>
        </q-markup-table>
      </div>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, nextTick, onMounted } from "vue";
import { useQuasar } from "quasar";
import { api } from 'boot/axios';

import { usePlayer } from 'stores/player';
import { usePlayerStatusStore } from 'stores/playerStatus';
import { useCurrentPlaylistStore } from 'stores/currentPlaylist';

const $q = useQuasar();

const currentPlaylist = useCurrentPlaylistStore();
const player = usePlayer();
const playerStatus = usePlayerStatusStore();

const inputSearchTextRef = ref(null);
const searchText = ref("");

const searchResults = ref([]);

const loading = ref(false);

const tab = ref('global');

const noResults = ref(false);

const allowSendToPlayList = ref(false);

const totalPages = ref(0);
const currentPageIndex = ref(1);

const sortField = ref('title');

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

function onPaginationChanged(pageIndex) {
  currentPageIndex.value = pageIndex;
  onSearch(false);
}

function onSortBy(field) {
  if (field == sortField.value) {
    sortOrder.value = sortOrder.value.value == 'DESC' ? sortOrderValues[0] : sortOrderValues[1];
  } else {
    sortOrder.value = sortOrderValues[0];
    sortField.value = field;
  }
  onSearch();
}

function onSearch(resetPager) {
  if (resetPager) {
    currentPageIndex.value = 1;
  }
  allowSendToPlayList.value = false;
  if (searchText.value && searchText.value.trim().length > 0) {
    loading.value = true;
    api.track.search({ text: searchText.value }, currentPageIndex.value, 16, false, sortField.value, sortOrder.value.value)
      .then((success) => {
        totalPages.value = success.data.data.pager.totalPages;
        searchResults.value = success.data.data.items.map((item) => { return ({ track: item }); });
        allowSendToPlayList.value = true;
        loading.value = false;
        nextTick(() => {
          inputSearchTextRef.value.$el.focus();
        });
      })
      .catch((error) => {
        loading.value = false;
        $q.notify({
          type: "negative",
          message: "API Error: error searching tracks",
          caption: "API Error: fatal error details: HTTP {" + error.response.status + "} ({" + error.response.statusText + "})"
        });
      });
  }
}

function onPlayTrack(track) {
  player.interact();
  currentPlaylist.saveElements([track]);
}

function onSendPlaylist() {
  player.interact();
  if (!playerStatus.isStopped) {
    player.stop();
  }
  currentPlaylist.saveElements(searchResults.value);
  allowSendToPlayList.value = false;
  nextTick(() => {
    player.play(true);
  });
}

function onToggleFavorite(track) {
    if (track && track.id) {
      //loading.value = true;
    const funct = track.favorited ? api.track.unSetFavorite: api.track.setFavorite;
    funct(track.id).then((success) => {
      track.favorited = success.data.favorited;
      // TODO use store
      //loading.value = false;
    })
      .catch((error) => {
        //loading.value = false;
        switch (error.response.status) {
          default:
            // TODO: custom message
            $q.notify({
              type: "negative",
              message: t("API Error: fatal error"),
              caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
            });
            break;
        }
      });
    }
}

onMounted(() => {
  nextTick(() => {
    inputSearchTextRef.value.$el.focus();
  });
});
</script>
