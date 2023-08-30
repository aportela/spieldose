<template>
  <component :is="dashboardBaseBlock" :icon="'format_list_numbered'" :title="title" :loading="loading" @refresh="refresh">
    <template #tabs>
      <q-tabs v-model="tab" no-caps class="text-pink-7 q-mb-md">
        <q-tab v-for="tabElement in entities" :key="tabElement.value" :name="tabElement.value"
          :label="tabElement.label" />
      </q-tabs>
    </template>
    <template #list>
      <ol class="pl-5 is-size-6-5" v-if="tab == 'tracks'">
        <DashboardBaseBlockListElementTrack v-for="item in items" :key="item.id" :track="item">
        </DashboardBaseBlockListElementTrack>
      </ol>
      <ol class="pl-5 is-size-6-5" v-else-if="tab == 'artists'">
        <DashboardBaseBlockListElementArtist v-for="item in items" :key="item.id" :artist="item">
        </DashboardBaseBlockListElementArtist>
      </ol>
      <ol class="pl-5 is-size-6-5" v-else-if="tab == 'albums'">
        <DashboardBaseBlockListElementAlbum v-for="item in items" :key="item.id" :album="item">
        </DashboardBaseBlockListElementAlbum>
      </ol>
      <ol class="pl-5 is-size-6-5" v-else-if="tab == 'genres'">
        <DashboardBaseBlockListElementGenre v-for="item in items" :key="item.id" :genre="item">
        </DashboardBaseBlockListElementGenre>
      </ol>
    </template>
  </component>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useQuasar } from "quasar";
import { default as dashboardBaseBlock } from 'components/DashboardBaseBlock.vue';
import { default as DashboardBaseBlockListElementTrack } from 'components/DashboardBaseBlockListElementTrack.vue';
import { default as DashboardBaseBlockListElementArtist } from 'components/DashboardBaseBlockListElementArtist.vue';
import { default as DashboardBaseBlockListElementAlbum } from 'components/DashboardBaseBlockListElementAlbum.vue';
import { default as DashboardBaseBlockListElementGenre } from 'components/DashboardBaseBlockListElementGenre.vue';
import { api } from 'boot/axios';
import { usePlayer } from 'stores/player';
import { useCurrentPlaylistStore } from 'stores/currentPlaylist';

const $q = useQuasar();
const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();

const loading = ref(false);
const items = ref([]);

const title = computed(() => {
  let str = null;
  if (props.played) {
      str = 'Recently played';
  } else if (props.added)  {
      str = 'Recently added';
  }
  return (str);
});

const tab = ref(null);

watch(tab, (newValue, oldValue) => {
  refresh();
});

const entities = [
  {
    label: 'Tracks',
    value: 'tracks',
    function: api.metrics.getTracks,
    listItemType: ''
  },
  {
    label: 'Artists',
    value: 'artists',
    function: api.metrics.getArtists,

  },
  {
    label: 'Albums',
    value: 'albums',
    function: api.metrics.getTracks,
  },
  {
    label: 'Genres',
    value: 'genres',
    function: api.metrics.getTracks,
  }
];

const props = defineProps({
  played: {
    type: Boolean
  },
  added: {
    type: Boolean
  }
})


const count = 5;
let filter = {
  entity: 'tracks'
};

function refresh() {
  if (tab.value) {
    const funct = entities.filter((entity) => entity.value == tab.value)[0].function;
    loading.value = true;
    funct(filter, 'recentlyAdded', count).then((success) => {
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
}

function refreshArtists() {
  loading.value = true;
  api.metrics.getArtists(filter, 'recentlyAdded', count).then((success) => {
    //items.value = success.data.data;
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
    $q.notify({
      type: "negative",
      message: "API Error: error loading top played artists metrics",
      caption: "API Error: fatal error details: HTTP {" + error.response.status + "} ({" + error.response.statusText + "})"
    });
  });
}

function playTrack(tracks) {
  player.stop();
  currentPlaylist.saveTracks(tracks);
  player.interact();
  player.play(false);
}

tab.value = 'tracks';


</script>
