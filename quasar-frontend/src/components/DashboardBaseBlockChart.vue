<template>
  <component :is="dashboardBaseBlock" :icon="icon || 'analytics'" :title="t('Play stats')" :loading="loading"
    @refresh="refresh">
    <template #tabs>
      <q-tabs v-model="tab" no-caps class="text-pink-7 q-mb-md">
        <q-tab v-for="tabElement in dateRanges" :key="tabElement.value" :name="tabElement.value"
          :label="t(tabElement.label)" />
      </q-tabs>
    </template>
    <template #chart>
      <div :id="divId" v-show="items && items.length > 0"></div>
      <div v-if="!loading">
        <h5 class="text-h5 text-center q-py-sm q-mt-xl q-mt-sm" v-if="loadingErrors"><q-icon name="error"
            size="xl"></q-icon> {{ t('Error loading data') }}</h5>
        <h5 class="text-h5 text-center q-py-sm q-mt-xl q-mt-sm" v-else-if="!(items && items.length > 0)"><q-icon
            name="warning" size="xl"></q-icon> {{ t('No enought data') }}</h5>
      </div>
    </template>
  </component>
</template>

<style lang="scss">
@import "~chartist/dist/index.css";
</style>

<script setup>
import { ref, watch, nextTick, computed, onMounted } from "vue";
import { useQuasar, uid } from "quasar";
import { useI18n } from 'vue-i18n'
import { BarChart, LineChart } from 'chartist';
import { default as dashboardBaseBlock } from 'components/DashboardBaseBlock.vue';
import { api } from 'boot/axios';

const $q = useQuasar();
const { t } = useI18n();

const divId = "ct-chart-" + uid();

// TODO custom div id
const loading = ref(false);
let items = [];

const tab = ref('hour');

const chart = ref(null);

watch(tab, (newValue) => {
  refresh();
});

const dateRanges = [
  {
    label: 'Hour',
    value: 'hour',
  },
  {
    label: 'Weekday',
    value: 'weekday'
  },
  {
    label: 'Month',
    value: 'month'
  },
  {
    label: 'Year',
    value: 'year'
  },
  {
    label: 'Always',
    value: 'fullDate'
  }
];

const props = defineProps({
  icon: {
    type: String,
    trackId: String
  },
  globalStats: Boolean
})

const useGlobalStats = computed(() => {
  return (props.globalStats || false);
});

watch(useGlobalStats, (newValue) => {
  refresh();
});

const defaultChartOptions = {
  low: 0,
  showArea: true,
  fullWidth: true,
  chartPadding: { left: 48, right: 48 },
  showPoint: false
};

const defaultChartOptionsWithoutLabels = {
  low: 0,
  showArea: true,
  fullWidth: true,
  chartPadding: { left: 48, right: 48 },
  showLabel: false,
  axisX: {
    showGrid: false,
    showLabel: false,
    offset: 0
  },
  showPoint: false
};

function drawChart() {
  // TODO: labels are not reactive on i18n changes
  let labels = [];
  let values = [];
  //  items.map((item) => { return (item.total); });
  switch (tab.value) {
    case 'hour':
      labels = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
      values = new Array(24).fill(0);
      items.forEach((item) => {
        values[new Number(item.hour)] = item.total;
      });
      break;
    case 'weekday':
      labels = [t('Sunday'), t('Monday'), t('Tuesday'), t('Wednesday'), t('Thursday'), t('Friday'), t('Saturday')];
      values = new Array(7).fill(0);
      // TODO: check correct index starting on sunday
      items.forEach((item) => {
        values[new Number(item.weekday)] = item.total;
      });
      break;
    case 'month':
      labels = [t('January'), t('February'), t('March'), t('April'), t('May'), t('June'), t('July'), t('August'), t('September'), t('October'), t('November'), t('December')]
      values = new Array(12).fill(0);
      items.forEach((item) => {
        values[new Number(item.month)] = item.total;
      });
      break;
    case 'year':
      labels = items.map((item) => { return (item.year); });
      values = items.map((item) => { return (item.total); });
      break;
    case 'fullDate':
      labels = items.map((item) => { return (item.fullDate); });
      values = items.map((item) => { return (item.total); });
      break;
  }
  if (chart.value) {
    chart.value.detach();
  }
  if (labels.length > 1) {
    chart.value = new LineChart('#' + divId, {
      labels: labels,
      series: [values],
   }, tab.value != 'fullDate' ? defaultChartOptions : defaultChartOptionsWithoutLabels);

  } else {
    chart.value = new BarChart('#' + divId, {
      labels: labels,
      series: [values],
    }, tab.value != 'fullDate' ? defaultChartOptions : defaultChartOptionsWithoutLabels);
  }
}

const loadingErrors = ref(false);

function refresh() {
  loadingErrors.value = false;
  if (tab.value) {
    loading.value = true;
    api.metrics.getDataRanges({ trackId: props.trackId || null, dateRange: tab.value, global: useGlobalStats.value }).then((success) => {
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

onMounted(() => {
  refresh();
});

</script>
