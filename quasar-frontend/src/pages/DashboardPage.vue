<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="analytics" label="Dashboard" />
      </q-breadcrumbs>
      <div class="row">
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockTopTracks></DashboardBaseBlockTopTracks>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockTopArtists></DashboardBaseBlockTopArtists>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockTopAlbums></DashboardBaseBlockTopAlbums>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockTopGenres></DashboardBaseBlockTopGenres>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockRecentlyTracks></DashboardBaseBlockRecentlyTracks>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockRecentlyArtists></DashboardBaseBlockRecentlyArtists>
        </div>
      </div>
      <component :is="dashboardBaseBlock" :icon="'analytics'" :title="'Play statistics'" @refresh="console.log(0)">
        <template #tabs>
          <component :is="DashboardBaseBlockTabs" tab-type="dateRanges" selected-tab="always"></component>
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
import { default as DashboardBaseBlockTopTracks } from 'components/DashboardBaseBlockTopTracks.vue';
import { default as DashboardBaseBlockTopArtists } from 'components/DashboardBaseBlockTopArtists.vue';
import { default as DashboardBaseBlockTopAlbums } from 'components/DashboardBaseBlockTopAlbums.vue';
import { default as DashboardBaseBlockTopGenres } from 'components/DashboardBaseBlockTopGenres.vue';
import { default as DashboardBaseBlockRecentlyTracks } from 'components/DashboardBaseBlockRecentlyTracks.vue';
import { default as DashboardBaseBlockRecentlyArtists } from 'components/DashboardBaseBlockRecentlyArtists.vue';
import { default as DashboardBaseBlockRecentlyAlbums } from 'components/DashboardBaseBlockRecentlyAlbums.vue';


import { BarChart } from 'chartist';



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

</script>
