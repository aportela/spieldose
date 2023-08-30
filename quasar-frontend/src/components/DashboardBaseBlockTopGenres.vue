<template>
  <component :is="dashboardBaseBlock" :icon="'format_list_numbered'" :title="'Top played genres'" :loading="loading"
    @refresh="refresh">
    <template #tabs>
      <component :is="DashboardBaseBlockTabs" tab-type="dateRanges" :selected-tab="currentTab" @change="onChangeDateFilter">
      </component>
    </template>
    <template #list>
      <ol class="pl-5 is-size-6-5" v-if="items && items.length > 0">
        <DashboardBaseBlockListElementGenre v-for="item in items" :key="item.name" :genre="item"></DashboardBaseBlockListElementGenre>
      </ol>
      <h5 class="text-h5 text-center" v-else-if="! loading"><q-icon name="warning" size="xl"></q-icon> No enought data</h5>
    </template>
  </component>
</template>

<script setup>
import { ref } from "vue";
import { useQuasar } from "quasar";
import { default as dashboardBaseBlock } from 'components/DashboardBaseBlock.vue';
import { default as DashboardBaseBlockTabs } from 'components/DashboardBaseBlockTabs.vue';
import { default as DashboardBaseBlockListElementGenre } from 'components/DashboardBaseBlockListElementGenre.vue';
import { api } from 'boot/axios';

const $q = useQuasar();

const loading = ref(false);
const currentTab = ref('always');
const items = ref([]);

const count = 5;
let filter = {
  fromDate: null,
  toDate: null
};

function refresh() {
  loading.value = true;
  api.metrics.getTopPlayedGenres(filter, count).then((success) => {
    items.value = success.data.data;
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
    $q.notify({
      type: "negative",
      message: "API Error: error loading top played genres metrics",
      caption: "API Error: fatal error details: HTTP {" + error.response.status + "} ({" + error.response.statusText + "})"
    });
  });
}

function onChangeDateFilter(d) {
  filter = d.filter;
  currentTab.value = d.tab;
  refresh();
}

refresh();
</script>
