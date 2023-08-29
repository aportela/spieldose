<template>
  <component :is="dashboardBaseBlock" :icon="'format_list_numbered'" :title="'Top played tracks'" :loading="loading"
    @refresh="refresh">
    <template #tabs>
      <component :is="DashboardBaseBlockTabs" tab-type="dateRanges" selected-tab="always" @change="onChangeDateFilter">
      </component>
    </template>
    <template #list>
      <ol class="pl-5 is-size-6-5">
        <li class="is-size-6-5" v-for="item in items" :key="item.id">
          <q-icon name="play_arrow" size="sm" title="play track" class="cursor-pointer" @click="playTrack([item])" />
          <span>{{ item.title }}</span>
          <span v-if="item.artistName"> / <router-link :to="{ name: 'artist', params: { name: item.artistName } }">{{
            item.artistName }}</router-link></span>
          <span> ({{ item.playCount }} plays)</span>
        </li>
      </ol>
    </template>
  </component>
</template>

<script setup>
import { ref } from "vue";
import { useQuasar } from "quasar";
import { default as dashboardBaseBlock } from 'components/DashboardBaseBlock.vue';
import { default as DashboardBaseBlockTabs } from 'components/DashboardBaseBlockTabs.vue';
import { api } from 'boot/axios';
import { usePlayer } from 'stores/player';
import { useCurrentPlaylistStore } from 'stores/currentPlaylist';

const $q = useQuasar();
const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();

const loading = ref(false);
const items = ref([]);

const count = 5;
let filter = {
  fromDate: null,
  toDate: null
};

function refresh() {
  loading.value = true;
  api.metrics.getTopPlayedTracks(filter, count).then((success) => {
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

function onChangeDateFilter(d) {
  filter = d.filter;
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
