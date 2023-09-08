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
      <div class="ct-chart" v-show="items && items.length > 0"></div>
      <h5 class="text-h5 text-center" v-if="!loading && !(items && items.length > 0)"><q-icon name="warning"
          size="xl"></q-icon> {{ t('No enought data') }}</h5>
    </template>
  </component>
</template>

<style lang="scss">
@import "~chartist/dist/index.css";
</style>

<script setup>
import { ref, watch, nextTick, computed } from "vue";
import { useQuasar } from "quasar";
import { useI18n } from 'vue-i18n'
import { BarChart, LineChart } from 'chartist';
import { default as dashboardBaseBlock } from 'components/DashboardBaseBlock.vue';
import { api } from 'boot/axios';

const $q = useQuasar();
const { t } = useI18n();

const loading = ref(false);
const items = ref([]);

const tab = ref(null);

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
  }
];

const props = defineProps({
  icon: {
    type: String,
  },
  globalStats: Boolean
})

const useGlobalStats = computed(() => {
  return(props.globalStats || false);
});

watch(useGlobalStats, (newValue) => {
  refresh();
});

const chartOptions = {
  low: 0,
  showArea: true,
  fullWidth: true,
  chartPadding: { left: 48, right: 48 }
};

function drawChart() {
  // TODO: labels are not reactive on i18n changes
  let labels = [];
  const values = items.value.map((item) => { return (item.total); });
  switch (tab.value) {
    case 'hour':
      labels = items.value.map((item) => { return (item.hour); });
      break;
    case 'weekday':
      labels = [t('Sunday'), t('Monday'), t('Tuesday'), t('Wednesday'), t('Thursday'), t('Friday'), t('Saturday')];
      break;
    case 'month':
      labels = [t('January'), t('February'), t('March'), t('April'), t('May'), t('June'), t('July'), t('August'), t('September'), t('October'), t('November'), t('December')]
      break;
    case 'year':
      labels = items.value.map((item) => { return (item.year); });
      break;
  }
  if (labels.length > 1) {
    new LineChart('.ct-chart', {
      labels: labels,
      series: [values],
    }, chartOptions);
  } else {
    new BarChart('.ct-chart', {
      labels: labels,
      series: [values],
    }, chartOptions);
  }
}

function refresh() {
  if (tab.value) {
    loading.value = true;
    api.metrics.getDataRanges({ dateRange: tab.value, global: useGlobalStats.value }).then((success) => {
      items.value = success.data.data;
      loading.value = false;
      nextTick(() => {
        drawChart();
      });
    }).catch((error) => {
      loading.value = false;
      $q.notify({
        type: "negative",
        message: "API Error: error loading metrics",
        caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
      });
    });
  }
}

tab.value = 'hour';

</script>
