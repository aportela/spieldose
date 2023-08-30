<template>
  <component :is="dashboardBaseBlock" :icon="'format_list_numbered'" :title="title" :loading="loading"
    @refresh="refresh">
    <template #tabs>
      <component :is="DashboardBaseBlockTabs" tab-type="entities" :selected-tab="currentTab" @change="onChangeEntityFilter">
      </component>
    </template>
    <template #list>
      <ol class="pl-5 is-size-6-5">
        <DashboardBaseBlockListElementTrack v-for="item in items" :key="item.id" :track="item"></DashboardBaseBlockListElementTrack>
      </ol>
    </template>
  </component>
</template>

<script setup>
import { ref } from "vue";
import { useQuasar } from "quasar";
import { default as dashboardBaseBlock } from 'components/DashboardBaseBlock.vue';
import { default as DashboardBaseBlockTabs } from 'components/DashboardBaseBlockTabs.vue';
import { default as DashboardBaseBlockListElementTrack } from 'components/DashboardBaseBlockListElementTrack.vue';
import { api } from 'boot/axios';
import { usePlayer } from 'stores/player';
import { useCurrentPlaylistStore } from 'stores/currentPlaylist';

const $q = useQuasar();
const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();

const loading = ref(false);
const currentTab = ref('tracks');
const items = ref([]);

const props = defineProps({
  title: {
    type: String,
    required: true
  }
})


const count = 5;
let filter = {
};

function refresh() {
  loading.value = true;
  api.metrics.getTracks(filter, 'recentlyAdded', count).then((success) => {
    items.value = success.data.data;
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
    $q.notify({
      type: "negative",
      message: "API Error: error loading top played tracks metrics",
      caption: "API Error: fatal error details: HTTP {" + error.response.status + "} ({" + error.response.statusText + "})"
    });
  });
}

function onChangeEntityFilter(d) {
  currentTab.value = d.tab;
  refresh();
}

function playTrack(tracks) {
  player.stop();
  currentPlaylist.saveTracks(tracks);
  player.interact();
  player.play(false);
}

refresh();
</script>
