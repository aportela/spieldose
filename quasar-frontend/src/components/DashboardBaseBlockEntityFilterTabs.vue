<template>
  <q-tabs v-model="tab" no-caps class="text-pink-7 q-mb-md">
    <q-tab name="tracks" label="Tracks" />
    <q-tab name="artists" label="Artists" />
    <q-tab name="albums" label="Albums" />
  </q-tabs>
</template>

<script setup>
import { ref, watch, emit } from 'vue'

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
    tracks: 'Tracks'
  },
  {
    artists: 'Artists'
  },
  {
    albums: 'Albums'
  },

];

const dateRanges = [
  {
    today: 'Today'
  },
  {
    lastWeek: 'Last week'
  },
  {
    lastMonth: 'Last month'
  },
  {
    lastYear: 'Last year'
  },
  {
    always: 'Always'
  },

];

const tab = ref(null);

switch (props.tabType) {
  case 'date':
    tab = props.selectedTab || 'always';
    break;
  case 'entities':
    tab = props.selectedTab || 'track';
    break;
}

watch(tab, (newValue, oldValue) => {
  emit('change', newValue)
});

</script>
