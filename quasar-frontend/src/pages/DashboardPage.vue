<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="analytics" label="Dashboard" />
      </q-breadcrumbs>
      <div class="row q-mb-lg">
        <div class="col-xl-4 col-lg-4 col-12">
          <component :is="dashboardBaseBlock" :icon="'format_list_numbered'" :title="'Top played tracks'"></component>
        </div>
        <div class="col-xl-4 col-lg-4 col-12">
          <component class="q-mx-lg" :is="dashboardBaseBlock" :icon="'format_list_numbered'" :title="'Top played albums'">
          </component>
        </div>
        <div class="col-xl-4 col-lg-4 col-12">
          <component :is="dashboardBaseBlock" :icon="'format_list_numbered'" :title="'Top played artists'"></component>
        </div>
      </div>
      <div class="row q-mb-lg">
        <div class="col-xl-4 col-lg-4 col-12">
          <component :is="dashboardBaseBlock" :icon="'format_list_numbered'" :title="'Top played genres'"></component>
        </div>
        <div class="col-xl-4 col-lg-4 col-12">
          <component class="q-mx-lg" :is="dashboardBaseBlock" :icon="'schedule'" :title="'Recently added'"></component>
        </div>
        <div class="col-xl-4 col-lg-4 col-12">
          <component :is="dashboardBaseBlock" :icon="'schedule'" :title="'Recently added'"></component>
        </div>
      </div>
      <component :is="dashboardBaseBlock" :icon="'analytics'" :title="'Play statistics'">
        <template v-slot:body>
          <q-tabs v-model="tab" no-caps class="text-pink-7 q-mb-md">
            <q-tab name="hour" label="By hour" />
            <q-tab name="weekday" label="By weekday" />
            <q-tab name="month" label="By month" />
            <q-tab name="year" label="By year" />
          </q-tabs>
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
import { BarChart } from 'chartist';

const tab = ref('weekday');

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
