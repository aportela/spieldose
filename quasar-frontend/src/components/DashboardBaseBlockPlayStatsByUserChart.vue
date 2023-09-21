<template>
  <component :is="dashboardBaseBlock" :icon="icon || 'analytics'" :title="t('User play stats')" :loading="loading"
    @refresh="refresh">
    <template #tabs>
      <q-tabs v-model="tab" no-caps class="text-pink-7 q-mb-md">
        <q-tab v-for="tabElement in dateRanges" :key="tabElement.value" :name="tabElement.value"
          :label="t(tabElement.label)" />
      </q-tabs>
    </template>
    <template #chart>
      <div id="ct-chart-play-stats-by-user" v-show="items && items.length > 0"></div>
      <div v-if="! loading">
        <h5 class="text-h5 text-center q-py-sm q-mt-xl q-mt-sm" v-if="loadingErrors"><q-icon name="error" size="xl"></q-icon> {{ t('Error loading data') }}</h5>
        <h5 class="text-h5 text-center q-py-sm q-mt-xl q-mt-sm" v-else-if=" !(items && items.length > 0)"><q-icon name="warning" size="xl"></q-icon> {{ t('No enought data') }}</h5>
      </div>
    </template>
  </component>
</template>

<style lang="scss">
@import "~chartist/dist/index.css";
</style>

<script setup>
import { ref, watch, nextTick, computed } from "vue";
import { date, useQuasar } from "quasar";
import { useI18n } from "vue-i18n"
import { BarChart, LineChart } from "chartist";
import { default as dashboardBaseBlock } from "components/DashboardBaseBlock.vue";
import { api } from "boot/axios";

const $q = useQuasar();
const { t } = useI18n();

const loading = ref(false);
let items = [];

const tab = ref(null);

const dateRanges = [
  {
    label: 'Today',
    value: 'today',
  },
  {
    label: 'Last week',
    value: 'lastWeek'
  },
  {
    label: 'Last month',
    value: 'lastMonth'
  },
  {
    label: 'Last year',
    value: 'lastYear'
  },
  {
    label: 'Always',
    value: 'always'
  }
];


let filter = {
  fromDate: null,
  toDate: null
};

watch(tab, (newValue) => {
  filter = {
    fromDate: null,
    toDate: null
  };
  switch (newValue) {
    case 'today':
      filter.fromDate = date.formatDate(Date.now(), 'YYYYMMDD');
      filter.toDate = date.formatDate(Date.now(), 'YYYYMMDD');
      break;
    case 'lastWeek':
      filter.fromDate = date.formatDate(date.addToDate(Date.now(), { days: -7 }), 'YYYYMMDD');
      filter.toDate = date.formatDate(Date.now(), 'YYYYMMDD');
      break;
    case 'lastMonth':
      filter.fromDate = date.formatDate(date.addToDate(Date.now(), { months: -1 }), 'YYYYMMDD');
      filter.toDate = date.formatDate(Date.now(), 'YYYYMMDD');
      break;
    case 'lastYear':
      filter.fromDate = date.formatDate(date.addToDate(Date.now(), { years: -1 }), 'YYYYMMDD');
      filter.toDate = date.formatDate(Date.now(), 'YYYYMMDD');
      break;
    case 'always':
      break;
  }
  refresh();
});

const props = defineProps({
  icon: {
    type: String,
  }
})

const chartOptions = {
  low: 0,
  showArea: true,
  fullWidth: true,
  chartPadding: { left: 48, right: 48 }
};

function drawChart() {
  // TODO: labels are not reactive on i18n changes
  let labels = [];
  let values = [];
  // TODO: slice if total > nn (max)
  // TODO: grow bar width
  items.forEach((item) => {
    labels.push(item.name);
    values.push(item.total);
  });
  new BarChart('#ct-chart-play-stats-by-user', {
    labels: labels,
    series: [values],
  }, chartOptions);
}

const loadingErrors = ref(false);

function refresh() {
  loadingErrors.value = false;
  if (tab.value) {
    loading.value = true;
    api.metrics.getMetricsByUser(filter).then((success) => {
      items = success.data.data;
      loading.value = false;
      nextTick(() => {
        drawChart();
      });
    }).catch((error) => {
      loadingErrors.value = true;
      loading.value = false;
      $q.notify({
        type: "negative",
        message: "API Error: error loading metrics",
        caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
      });
    });
  }
}

tab.value = 'lastWeek';

</script>
