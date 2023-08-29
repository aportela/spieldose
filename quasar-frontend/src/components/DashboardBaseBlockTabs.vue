<template>
  <q-tabs v-model="tab" no-caps class="text-pink-7 q-mb-md">
    <q-tab v-for="tabElement in tabElements" :key="tabElement.value" :name="tabElement.value" :label="tabElement.label" />
  </q-tabs>
</template>

<script setup>
import { ref, watch } from 'vue'
import { date } from "quasar";

const props = defineProps({
  tabType: {
    type: String
  },
  selectedTab: {
    type: String
  }
})

const emit = defineEmits(['change']);

const entities = [
  {
    label: 'Tracks',
    value: 'tracks'
  },
  {
    label: 'Artists',
    value: 'artists'
  },
  {
    label: 'Albums',
    value: 'albums'
  },
];

const dateRanges = [
  {
    label: 'Today',
    value: 'today'
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
  },
];

const tab = ref(null);

const tabElements = ref([]);

switch (props.tabType) {
  case 'dateRanges':
    tab.value = props.selectedTab || 'always';
    tabElements.value = dateRanges;
    break;
  case 'entities':
    tab.value = props.selectedTab || 'tracks';
    tabElements.value = entities;
    break;
}

watch(tab, (newValue, oldValue) => {
  if (props.tabType == 'dateRanges') {
    let filter = {
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
    emit('change', { tab: newValue, filter: filter });
  } else if (props.tabType == 'entities') {
    emit('change', { tab: newValue });
  }
});

</script>
