<template>
  <component :is="dashboardBaseBlock" :icon="icon || 'analytics'" title="Play stats" :loading="loading"
    @refresh="refresh">
    <template #tabs>
      <q-tabs v-model="tab" no-caps class="text-pink-7 q-mb-md">
        <q-tab v-for="tabElement in dateRanges" :key="tabElement.value" :name="tabElement.value"
          :label="tabElement.label" />
      </q-tabs>
    </template>
    <template #chart>
      <div class="ct-chart"></div>
    </template>
  </component>
</template>

<style lang="scss">
@import "~chartist/dist/index.css";
</style>

<script setup>
import { ref, watch, nextTick } from "vue";
import { useQuasar } from "quasar";
import { BarChart, LineChart } from 'chartist';
import { default as dashboardBaseBlock } from 'components/DashboardBaseBlock.vue';
import { api } from 'boot/axios';

const $q = useQuasar();

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
    type: String
  }
})

function drawChart() {
  let labels = [];
  let values = items.value.map((item) => { return (item.total); });
  switch (tab.value) {
    case 'hour':
      labels = items.value.map((item) => { return (item.hour); });
      break;
    case 'weekday':
      labels = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
      break;
    case 'month':
      labels = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december']
      break;
    case 'year':
      labels = items.value.map((item) => { return (item.year); });
      break;
  }
  if (labels.length > 1) {
    new LineChart('.ct-chart', {
      labels: labels,
      series: [values]
    }, {
      low: 0,
      showArea: true,
      fullWidth: true,
      chartPadding: {
        right: 40
      }
    });
  } else {
    new BarChart('.ct-chart', {
      labels: labels,
      series: [values]
    }, {
      low: 0,
      showArea: true,
      fullWidth: true,
      chartPadding: {
        right: 40,
      }
    });
  }
}

function refresh() {
  if (tab.value) {
    loading.value = true;
    api.metrics.getDataRanges(tab.value).then((success) => {
      items.value = success.data.data;
      loading.value = false;
      nextTick(() => {
        drawChart();
      });
    }).catch((error) => {
      loading.value = false;
      $q.notify({
        type: "negative",
        message: "API Error: error loading chart metrics",
        caption: "API Error: fatal error details: HTTP {" + error.response.status + "} ({" + error.response.statusText + "})"
      });
    });
  }
}

tab.value = 'hour';

</script>
