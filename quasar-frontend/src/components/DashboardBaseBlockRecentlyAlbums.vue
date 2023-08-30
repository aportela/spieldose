<template>
  <component :is="dashboardBaseBlock" :icon="'format_list_numbered'" :title="'Recently played albums'" :loading="loading"
    @refresh="refresh">
    <template #tabs>
      <component :is="DashboardBaseBlockTabs" tab-type="dateRanges" :selected-tab="currentTab" @change="onChangeDateFilter">
      </component>
    </template>
    <template #list>
      <ol class="pl-5 is-size-6-5">
        <DashboardBaseBlockListElementAlbum v-for="item in items" :key="item.id" :album="item"></DashboardBaseBlockListElementAlbum>
      </ol>
    </template>
  </component>
</template>

<script setup>
import { ref } from "vue";
import { useQuasar } from "quasar";
import { default as dashboardBaseBlock } from 'components/DashboardBaseBlock.vue';
import { default as DashboardBaseBlockTabs } from 'components/DashboardBaseBlockTabs.vue';
import { default as DashboardBaseBlockListElementAlbum } from 'components/DashboardBaseBlockListElementAlbum.vue';
import { api } from 'boot/axios';
import { usePlayer } from 'stores/player';
import { useCurrentPlaylistStore } from 'stores/currentPlaylist';

const $q = useQuasar();
const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();

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
  api.metrics.getTopPlayedAlbums(filter, count).then((success) => {
    items.value = success.data.data;
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
    $q.notify({
      type: "negative",
      message: "API Error: error loading top played albums metrics",
      caption: "API Error: fatal error details: HTTP {" + error.response.status + "} ({" + error.response.statusText + "})"
    });
  });
}

function onChangeDateFilter(d) {
  filter = d.filter;
  currentTab.value = d.tab;
  refresh();
}

function playAlbum(album) {
  console.log("TODO, GET & PLAY TRACKS")
}

refresh();
</script>
