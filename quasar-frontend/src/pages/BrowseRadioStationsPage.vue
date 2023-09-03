<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="radio" label="Browse radio stations" />
      </q-breadcrumbs>
      <q-card-section v-if="radioStations">
        <q-input v-model="radioStationName" rounded clearable type="search" outlined dense placeholder="Text condition"
          hint="Search radio stations with name" :loading="loading" :disable="loading"
          @keydown.enter.prevent="search(true)" @clear="noradioStationsFound = false; search(true)"
          :error="noradioStationsFound" :errorMessage="'No radio stations found with specified condition'">
          <template v-slot:prepend>
            <q-icon name="filter_alt" />
          </template>
          <template v-slot:append>
            <q-icon name="search" class="cursor-pointer" @click="search" />
          </template>
        </q-input>
        <div class="q-pa-lg flex flex-center" v-if="totalPages > 1">
          <q-pagination v-model="currentPageIndex" color="dark" :max="totalPages" :max-pages="5" boundary-numbers
            direction-links boundary-links @update:model-value="onPaginationChanged" :disable="loading" />
        </div>
        <div class="q-gutter-md row items-start">
          <div v-for="radioStation in radioStations" :key="radioStation.id" :radioStation="radioStation" class="cursor-pointer" @click="onPlayRadioStation(radioStation)">
            <q-img img-class="radiostation_image" :src="radioStation.image || '#'" width="250px" height="250px" fit="cover">
              <div class="absolute-bottom text-subtitle1 text-center">
                {{ radioStation.name }}
              </div>
              <template v-slot:error>
                <div class="absolute-full flex flex-center bg-grey-3 text-dark">
                  <div class="absolute-bottom text-subtitle1 text-center bg-grey-5 q-py-md">
                    {{ radioStation.name }}
                  </div>
                </div>
              </template>
            </q-img>
          </div>
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<style>
img.radiostation_image {
  opacity: 0.5;
  -webkit-filter: grayscale(100%); /* Safari 6.0 - 9.0 */
  filter: grayscale(100%) blur(2px);
  transition: filter 0.2s ease-in;
}

img.radiostation_image:hover {
  opacity: 1;
  -webkit-filter: none; /* Safari 6.0 - 9.0 */
  filter: none;
  transition: filter 0.2s ease-out;

}
</style>


<script setup>

import { ref } from "vue";
import { api } from 'boot/axios'
import { useQuasar } from "quasar";

import { usePlayer } from 'stores/player';
import { useCurrentPlaylistStore } from 'stores/currentPlaylist'

const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();


const $q = useQuasar();

const radioStationName = ref(null);
const noradioStationsFound = ref(false);
const loading = ref(false);
const radioStations = ref([]);

const totalPages = ref(0);
const currentPageIndex = ref(1);


function search(resetPager) {
  if (resetPager) {
    currentPageIndex.value = 1;
  }
  noradioStationsFound.value = false;
  loading.value = true;
  api.radioStation.search(currentPageIndex.value, 32, { name: radioStationName.value }).then((success) => {
    radioStations.value = success.data.data.items;
    totalPages.value = success.data.data.pager.totalPages;
    if (radioStationName.value && success.data.data.pager.totalResults < 1) {
      noradioStationsFound.value = true;
    }
    loading.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: "API Error: error loading radio stations",
      caption: "API Error: fatal error details: HTTP {" + error.response.status + "} ({" + error.response.statusText + "})"
    });
    loading.value = false;
  });
}

function onPaginationChanged(pageIndex) {
  currentPageIndex.value = pageIndex;
  search(false);
}

function onPlayRadioStation(radioStation) {
  player.interact();
  currentPlaylist.saveTracks([ { radioStation: radioStation }]);
}
search(true);

</script>
