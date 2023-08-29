<template>
  <component :is="dashboardBaseBlock" :icon="'format_list_numbered'" :title="'Top played genres'" :loading="loading" @refresh="refresh">
    <template #tabs>
      <component :is="DashboardBaseBlockTabs" tab-type="dateRanges" selected-tab="always" @change="refresh">
      </component>
    </template>
    <template #list>
      <ol class="pl-5 is-size-6-5">
        <li class="is-size-6-5" v-for="item in items" :key="item.id">
          <q-icon name="link" size="sm" />
          <span>{{ item.name }}</span>
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

function refresh() {
  loading.value = true;
  api.metrics.getTopPlayedGenres().then((success) => {
    items.value = success.data.data;
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
    $q.notify({
      type: "negative",
      message: "API Error: error loading top played genres metrics",
      caption: "API Error: fatal error details: HTTP {" + error.response.status + "} ({" + error.response.statusText + "})"
    });
  });
}

function playAlbum(album) {
  console.log("TODO, GET & PLAY TRACKS")
}

refresh();
</script>
