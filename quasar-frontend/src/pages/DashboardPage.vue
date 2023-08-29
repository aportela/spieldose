<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="analytics" label="Dashboard" />
      </q-breadcrumbs>
      <div class="row q-mb-lg">
        <div class="col-xl-4 col-lg-4 col-12">
          <DashboardBaseBlockTopTracks></DashboardBaseBlockTopTracks>
        </div>
        <div class="col-xl-4 col-lg-4 col-12">
          <DashboardBaseBlockTopArtists className="q-mx-lg"></DashboardBaseBlockTopArtists>
        </div>
        <div class="col-xl-4 col-lg-4 col-12">
          <DashboardBaseBlockTopAlbums></DashboardBaseBlockTopAlbums>
        </div>
      </div>
      <div class="row q-mb-lg">
        <div class="col-xl-4 col-lg-4 col-12">
          <DashboardBaseBlockTopGenres></DashboardBaseBlockTopGenres>
        </div>
        <div class="col-xl-4 col-lg-4 col-12">
          <component class="q-mx-lg" :is="dashboardBaseBlock" :icon="'schedule'" :title="'Recently added'"
            @refresh="onRefreshRecentlyAdded">
            <template #tabs>
              <component :is="DashboardBaseBlockTabs" tab-type="entities" selected-tab="tracks"
                @change="onChangeEntityFilter"></component>
            </template>
            <template #list>
              <DashboardBaseBlockList  items-type="tracks" :items="recentlyAdded"></DashboardBaseBlockList>
            </template>
          </component>
        </div>
        <div class="col-xl-4 col-lg-4 col-12">
          <component :is="dashboardBaseBlock" :icon="'schedule'" :title="'Recently played'" @refresh="onRefreshRecentlyPlayed">
            <template #tabs>
              <component :is="DashboardBaseBlockTabs" tab-type="entities" selected-tab="tracks"
                @change="onChangeEntityFilter"></component>
            </template>
            <template #list>
              <DashboardBaseBlockList  items-type="tracks" :items="recentlyPlayed"></DashboardBaseBlockList>
            </template>
          </component>
        </div>
      </div>
      <component :is="dashboardBaseBlock" :icon="'analytics'" :title="'Play statistics'" @refresh="console.log(0)">
        <template #tabs>
          <component :is="DashboardBaseBlockTabs" tab-type="dateRanges" selected-tab="always"
            @change="onChangeDateFilter"></component>
        </template>
        <template #chart>
          <div class="ct-chart"></div>
        </template>
      </component>
    </q-card>
  </q-page>
</template>

<style lang="scss">
@import "~chartist/dist/index.css";
</style>

<script setup>
import { ref, onMounted } from "vue";
import { default as dashboardBaseBlock } from 'components/DashboardBaseBlock.vue';
import { default as DashboardBaseBlockTabs } from 'components/DashboardBaseBlockTabs.vue';
import { default as DashboardBaseBlockList } from 'components/DashboardBaseBlockList.vue';
import { default as DashboardBaseBlockTopTracks } from 'components/DashboardBaseBlockTopTracks.vue';
import { default as DashboardBaseBlockTopArtists } from 'components/DashboardBaseBlockTopArtists.vue';
import { default as DashboardBaseBlockTopAlbums } from 'components/DashboardBaseBlockTopAlbums.vue';
import { default as DashboardBaseBlockTopGenres } from 'components/DashboardBaseBlockTopGenres.vue';
import { api } from 'boot/axios';
import { BarChart } from 'chartist';

const loading = ref(false);

const topPlayedTracks = ref([]);
const topPlayedAlbums = ref([]);
const topPlayedArtists = ref([]);
const topPlayedGenres = ref([]);
const recentlyAdded = ref([]);
const recentlyPlayed = ref([]);

onMounted(() => {
  new BarChart('.ct-chart', {
    labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
    series: [
      [12, 9, 7, 8, 5, 4, 3]
    ]
  }, {
    fullWidth: true,
    chartPadding: {
      right: 40
    }
  });
});

function onChangeDateFilter(data) {
  //refresh();
}

function onChangeEntityFilter(data) {
  //refresh();
}

function onRefreshTopPlayedTracks() {
  refreshTopPlayedTracks();
}

function refreshTopPlayedTracks() {
  loading.value = true;
  api.metrics.getTopPlayedTracks().then((success) => {
    topPlayedTracks.value = success.data.data;
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
  });
}

function onRefreshTopPlayedAlbums() {
  refreshTopPlayedAlbums();
}

function refreshTopPlayedAlbums() {
  loading.value = true;
  api.metrics.getTopPlayedTracks().then((success) => {
    topPlayedAlbums.value = success.data.data;
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
  });
}

function onRefreshTopPlayedArtists() {
  refreshTopPlayedArtists();
}

function refreshTopPlayedArtists() {
  loading.value = true;
  api.metrics.getTopPlayedTracks().then((success) => {
    topPlayedArtists.value = success.data.data;
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
  });
}

function onRefreshTopPlayedGenres() {
  refreshTopPlayedGenres();
}

function refreshTopPlayedGenres() {
  loading.value = true;
  api.metrics.getTopPlayedTracks().then((success) => {
    topPlayedGenres.value = success.data.data;
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
  });
}

function onRefreshRecentlyAdded() {
  refreshRecentlyAdded();
}

function refreshRecentlyAdded() {
  loading.value = true;
  api.metrics.getTopPlayedTracks().then((success) => {
    recentlyAdded.value = success.data.data;
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
  });
}

function onRefreshRecentlyPlayed() {
  refreshRecentlyPlayed();
}

function refreshRecentlyPlayed() {
  loading.value = true;
  api.metrics.getTopPlayedTracks().then((success) => {
    recentlyPlayed.value = success.data.data;
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
  });
}

refreshTopPlayedTracks();
refreshTopPlayedAlbums();
refreshTopPlayedArtists();
refreshTopPlayedGenres();
refreshRecentlyAdded();
refreshRecentlyPlayed();
</script>
