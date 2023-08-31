<template>
  <component :is="dashboardBaseBlock" :icon="'format_list_numbered'" :title="title" :loading="loading" @refresh="refresh">
    <template #tabs>
      <q-tabs v-model="tab" no-caps class="text-pink-7 q-mb-md">
        <q-tab v-for="tabElement in dateRanges" :key="tabElement.value" :name="tabElement.value"
          :label="tabElement.label" />
      </q-tabs>
    </template>
    <template #list>
      <ol class="pl-5 is-size-6-5" v-if="entity == 'tracks'">
        <DashboardBaseBlockListElementTrack v-for="item in items" :key="item.id" :track="item">
          <template #append>
            <span class="q-ml-sm">{{ item.playCount }} plays</span>
          </template>
        </DashboardBaseBlockListElementTrack>
      </ol>
      <ol class="pl-5 is-size-6-5" v-else-if="entity == 'artists'">
        <DashboardBaseBlockListElementArtist v-for="item in items" :key="item.id" :artist="item">
          <template #append>
            <span class="q-ml-sm">{{ item.playCount }} plays</span>
          </template>
        </DashboardBaseBlockListElementArtist>
      </ol>
      <ol class="pl-5 is-size-6-5" v-else-if="entity == 'albums'">
        <DashboardBaseBlockListElementAlbum v-for="item in items" :key="item.id" :album="item">
          <template #append>
            <span class="q-ml-sm">{{ item.playCount }} plays</span>
          </template>
        </DashboardBaseBlockListElementAlbum>
      </ol>
      <ol class="pl-5 is-size-6-5" v-else-if="entity == 'genres'">
        <DashboardBaseBlockListElementGenre v-for="item in items" :key="item.id" :genre="item">
          <template #append>
            <span class="q-ml-sm">{{ item.playCount }} plays</span>
          </template>
        </DashboardBaseBlockListElementGenre>
      </ol>
    </template>
  </component>
</template>

<script setup>
import { ref, watch } from "vue";
import { date, useQuasar } from "quasar";
import { default as dashboardBaseBlock } from 'components/DashboardBaseBlock.vue';
import { default as DashboardBaseBlockListElementTrack } from 'components/DashboardBaseBlockListElementTrack.vue';
import { default as DashboardBaseBlockListElementArtist } from 'components/DashboardBaseBlockListElementArtist.vue';
import { default as DashboardBaseBlockListElementAlbum } from 'components/DashboardBaseBlockListElementAlbum.vue';
import { default as DashboardBaseBlockListElementGenre } from 'components/DashboardBaseBlockListElementGenre.vue';
import { default as LabelTimestampAgo } from "components/LabelTimestampAgo.vue";
import { api } from 'boot/axios';

const $q = useQuasar();

const loading = ref(false);
const items = ref([]);

const tab = ref(null);

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
  },
];

const props = defineProps({
  entity: {
    type: String,
    required: true
  }
})

const count = 5;

let apiFunction = null;
let title = null;
switch (props.entity) {
  case 'tracks':
    title = 'Top played tracks';
    apiFunction = api.metrics.getTracks;
    break;
  case 'artists':
    title = 'Top played artists';
    apiFunction = api.metrics.getArtists;
    break;
  case 'albums':
    title = 'Top played albums';
    apiFunction = api.metrics.getAlbums;
    break;
  case 'genres':
    title = 'Top played genres';
    apiFunction = api.metrics.getGenres;
    break;
}
function refresh() {
  if (tab.value) {
    loading.value = true;
    apiFunction(filter, 'playCount', count).then((success) => {
      items.value = success.data.data;
      loading.value = false;
    }).catch((error) => {
      loading.value = false;
      $q.notify({
        type: "negative",
        message: "API Error: error loading top played metrics",
        caption: "API Error: fatal error details: HTTP {" + error.response.status + "} ({" + error.response.statusText + "})"
      });
    });
  }
}

tab.value = 'always';


</script>
