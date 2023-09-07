<template>
  <component :is="dashboardBaseBlock" :icon="icon || 'format_list_numbered'" :title="title" :loading="loading"
    @refresh="refresh">
    <template #tabs>
      <q-tabs v-model="tab" no-caps class="text-pink-7 q-mb-md">
        <q-tab v-for="tabElement in dateRanges" :key="tabElement.value" :name="tabElement.value"
          :label="t(tabElement.label)" />
      </q-tabs>
    </template>
    <template #list>
      <ol class="q-px-sm" v-if="entity == 'tracks'">
        <DashboardBaseBlockListElementTrack v-for="item in items" :key="item.id" :track="item.track">
          <template #append>
            <span class="q-ml-sm">{{ item.playCount }} {{ t(item.playCount > 1 ? 'nPlayCounts' : 'onePlayCount') }}</span>
          </template>
        </DashboardBaseBlockListElementTrack>
      </ol>
      <ol class="q-px-sm" v-else-if="entity == 'artists'">
        <DashboardBaseBlockListElementArtist v-for="item in items" :key="item.id" :artist="item">
          <template #append>
            <span class="q-ml-sm">{{ item.playCount }} {{ t(item.playCount > 1 ? 'nPlayCounts' : 'onePlayCount') }}</span>
          </template>
        </DashboardBaseBlockListElementArtist>
      </ol>
      <ol class="q-px-sm" v-else-if="entity == 'albums'">
        <DashboardBaseBlockListElementAlbum v-for="item in items" :key="item.id" :album="item">
          <template #append>
            <span class="q-ml-sm">{{ item.playCount }} {{ t(item.playCount > 1 ? 'nPlayCounts' : 'onePlayCount') }}</span>
          </template>
        </DashboardBaseBlockListElementAlbum>
      </ol>
      <ol class="q-px-sm" v-else-if="entity == 'genres'">
        <DashboardBaseBlockListElementGenre v-for="item in items" :key="item.id" :genre="item">
          <template #append>
            <span class="q-ml-sm">{{ item.playCount }} {{ t(item.playCount > 1 ? 'nPlayCounts' : 'onePlayCount') }}</span>
          </template>
        </DashboardBaseBlockListElementGenre>
      </ol>
      <h5 class="text-h5 text-center q-py-sm q-mt-xl q-mt-sm" v-if="!loading && !(items && items.length > 0)"><q-icon name="warning"
          size="xl"></q-icon> {{ t('No enought data') }}</h5>
    </template>
  </component>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { date, useQuasar } from "quasar";
import { useI18n } from 'vue-i18n'
import { default as dashboardBaseBlock } from 'components/DashboardBaseBlock.vue';
import { default as DashboardBaseBlockListElementTrack } from 'components/DashboardBaseBlockListElementTrack.vue';
import { default as DashboardBaseBlockListElementArtist } from 'components/DashboardBaseBlockListElementArtist.vue';
import { default as DashboardBaseBlockListElementAlbum } from 'components/DashboardBaseBlockListElementAlbum.vue';
import { default as DashboardBaseBlockListElementGenre } from 'components/DashboardBaseBlockListElementGenre.vue';
import { default as LabelTimestampAgo } from "components/LabelTimestampAgo.vue";
import { api } from 'boot/axios';

const $q = useQuasar();
const { t } = useI18n();

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
  icon: {
    type: String
  },
  entity: {
    type: String
  }
})

const title = computed(() => {
  switch (props.entity) {
    case 'tracks':
      return (t('Top played tracks'));
      break;
    case 'artists':
      return (t('Top played artists'));
      break;
    case 'albums':
      return (t('Top played albums'));
      break;
    case 'genres':
      return (t('Top played genres'));
      break;
    default:
      return (null);
      break;
  }
});

const count = 5;

let apiFunction = null;

switch (props.entity) {
  case 'tracks':
    apiFunction = api.metrics.getTracks;
    break;
  case 'artists':
    apiFunction = api.metrics.getArtists;
    break;
  case 'albums':
    apiFunction = api.metrics.getAlbums;
    break;
  case 'genres':
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
        message: "API Error: error loading metrics",
        caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
      });
    });
  }
}

tab.value = 'always';


</script>
