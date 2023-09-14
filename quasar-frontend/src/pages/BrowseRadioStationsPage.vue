<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <q-breadcrumbs-el icon="radio" label="Browse radio stations" />
    </q-breadcrumbs>
    <q-card-section v-if="radioStations">
      <div class="row q-gutter-xs">
        <div class="col">
          <q-input v-model="personalRadioStationName" clearable type="search" outlined dense placeholder="Text condition"
            hint="Search radio stations with name" :loading="loading" :disable="loading"
            @keydown.enter.prevent="search(true)" @clear="noRadioStationsFound = false; search(true)"
            :error="noRadioStationsFound" :errorMessage="'No radio stations found with specified condition'"
            ref="personalRadioStationNameRef">
            <template v-slot:prepend>
              <q-icon name="filter_alt" />
            </template>
            <template v-slot:append>
              <q-icon name="search" class="cursor-pointer" @click="search" />
            </template>
          </q-input>
        </div>
        <div class="col-xl-2">
          <q-select label="Country" dense outlined :options="['Spain', 'Portugal', 'France']"
            v-model="country"></q-select>
        </div>
        <div class="col-xl-2">
          <q-select label="Language" dense outlined :options="['English', 'Spanish', 'Galician']"
            v-model="language"></q-select>
        </div>
        <div class="col-xl-2">
          <q-select label="Tags" dense outlined :options="['news', 'music', 'rock']" multiple v-model="tags"></q-select>
        </div>
      </div>
      <div class="q-pa-lg flex flex-center" v-if="totalPages > 1">
        <q-pagination v-model="currentPageIndex" color="dark" :max="totalPages" :max-pages="5" boundary-numbers
          direction-links boundary-links @update:model-value="onPaginationChanged" :disable="loading" />
      </div>
      <div class="q-gutter-md row items-start">
        <div v-for="radioStation in radioStations" :key="radioStation.id" :radioStation="radioStation"
          class="cursor-pointer" @click="onPlayRadioStation(radioStation)">
          <q-img img-class="radiostation_image" :src="radioStation.images.normal || '#'" width="300px" height="300px"
            fit="cover">
            <div class="absolute-bottom text-subtitle1 text-center">
              <p class="q-mt-none">{{ radioStation.name }}</p>
              <q-chip size="md" icon="tag" truncate-chip-labels2 v-for="tag in radioStation.tags" :key="tag">{{ tag
              }}</q-chip>
            </div>
            <template v-slot:loading>
              <div class="absolute-full flex flex-center bg-grey-3 text-dark">
                <q-spinner color="pink" size="xl" />
                <div class="absolute-bottom text-subtitle1 text-center bg-grey-5 q-py-md">
                  <p class="q-mt-none">{{ radioStation.name }}</p>
                  <q-chip size="md" icon="tag" truncate-chip-labels v-for="tag in radioStation.tags" :key="tag">{{
                    tag }}</q-chip>
                </div>
              </div>
            </template>
            <template v-slot:error>
              <div class="absolute-full flex flex-center bg-grey-3 text-dark">
                <div class="absolute-bottom text-subtitle1 text-center bg-grey-5 q-py-md">
                  <p class="q-mt-none">{{ radioStation.name }}</p>
                  <q-chip size="md" icon="tag" truncate-chip-labels v-for="tag in radioStation.tags" :key="tag">{{
                    tag }}</q-chip>
                </div>
              </div>
            </template>
          </q-img>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<style>
img.radiostation_image {
  opacity: 0.5;
  -webkit-filter: grayscale(100%);
  /* Safari 6.0 - 9.0 */
  filter: grayscale(100%) blur(2px);
  transition: filter 0.2s ease-in;
}

img.radiostation_image:hover {
  opacity: 1;
  -webkit-filter: none;
  /* Safari 6.0 - 9.0 */
  filter: none;
  transition: filter 0.2s ease-out;

}
</style>

<script setup>

import { ref } from "vue";
import { api } from 'boot/axios'
import { useQuasar } from "quasar";
import { useI18n } from 'vue-i18n';

import { usePlayer } from 'stores/player';
import { useCurrentPlaylistStore } from 'stores/currentPlaylist';

const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();

const $q = useQuasar();
const { t } = useI18n();

const country = ref(null);
const language = ref(null);
const tags = ref([]);

const personalRadioStationName = ref(null);
const noRadioStationsFound = ref(false);
const loading = ref(false);
let radioStations = [];

const totalPages = ref(0);
const currentPageIndex = ref(1);

function search(resetPager) {
  if (resetPager) {
    //currentPageIndex.value = 1;
  }
  //noRadioStationsFound.value = false;
  loading.value = true;
  api.radioStation.search(currentPageIndex.value, 32, { name: personalRadioStationName.value }).then((success) => {
    radioStations = success.data.data.items;
    /*
    totalPages.value = success.data.data.pager.totalPages;
    if (personalRadioStationName.value && success.data.data.pager.totalResults < 1) {
      noRadioStationsFound.value = true;
    }
    */
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
  currentPlaylist.saveElements([{ radioStation: radioStation }]);
}

search(true);


</script>
