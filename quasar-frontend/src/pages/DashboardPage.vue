<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="analytics" label="Dashboard" />
      </q-breadcrumbs>
      <div class="row">
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockTop entity="tracks"></DashboardBaseBlockTop>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockTop entity="artists"></DashboardBaseBlockTop>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockTop entity="albums"></DashboardBaseBlockTop>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockTop entity="genres"></DashboardBaseBlockTop>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockRecently :played="true"></DashboardBaseBlockRecently>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockRecently :added="true"></DashboardBaseBlockRecently>
        </div>
      </div>
      <component :is="dashboardBaseBlock" :icon="'analytics'" :title="'Play statistics'" @refresh="console.log(0)">
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
import { default as DashboardBaseBlockTop } from 'components/DashboardBaseBlockTop.vue';
import { default as DashboardBaseBlockRecently } from 'components/DashboardBaseBlockRecently.vue';

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
