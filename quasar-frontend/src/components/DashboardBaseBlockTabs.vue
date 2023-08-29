<template>
  <q-tabs v-model="tab" no-caps class="text-pink-7 q-mb-md">
    <q-tab v-for="tabElement in tabElements" :key="tabElement.value" :name="tabElement.value" :label="tabElement.label" />
  </q-tabs>
</template>

<script setup>
import { ref, watch } from 'vue'

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
  emit('change', newValue)
});

</script>
