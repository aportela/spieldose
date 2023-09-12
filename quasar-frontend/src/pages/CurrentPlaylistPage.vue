<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <q-breadcrumbs-el icon="list_alt" :label="t('Current playlist')" />
    </q-breadcrumbs>
    <q-btn-group spread class="q-mb-md">
      <q-btn outline color="dark" :label="t('Clear')" icon="clear" @click="onClear"
        :disable="loading || !(elements && elements.length > 0)">
      </q-btn>
      <q-btn outline color="dark" :label="t('Randomize')" icon="bolt" @click="onRandom" :disable="loading">
      </q-btn>
      <q-btn outline color="dark" :label="t('Previous')" icon="skip_previous" @click="onPreviusPlaylist"
        :disable="loading || !currentPlaylist.allowSkipPrevious" />
      <q-btn outline color="dark" :label="t('Play')" icon="play_arrow" @click="onPlay"
        :disable="loading || !currentPlaylist.hasElements" v-if="playerStatus.isStopped" />
      <q-btn outline color="dark" :label="t('Pause')" icon="pause" @click="onPause"
        :disable="loading || !(elements && elements.length > 0)" v-else-if="playerStatus.isPlaying" />
      <q-btn outline color="dark" :label="t('Resume')" icon="play_arrow" @click="onResume"
        :disable="loading || !(elements && elements.length > 0)" v-else-if="playerStatus.isPaused" />
      <q-btn outline color="dark" :label="t('Stop')" icon="stop" @click="onStop"
        :disable="loading || playerStatus.isStopped || !(elements && elements.length > 0)" />
      <q-btn outline color="dark" :label="t('Next')" icon="skip_next" @click="onNextPlaylist"
        :disable="loading || !currentPlaylist.allowSkipNext" />
      <q-btn outline color="dark" :label="t('Download')" icon="save_alt"
        :disable="loading || !currentPlaylist.getCurrentElementURL" :href="currentPlaylist.getCurrentElementURL" />
      <q-btn outline color="dark" :label="t('Save as')" icon="save_alt"
        :disable="loading || !(elements && elements.length > 0)" @click="onSavePlaylist" />
    </q-btn-group>
    <q-table class="my-sticky-header-table" style="height: 46.8em" title="Current playlist" :rows="rows"
      :columns="columns" row-key="id" virtual-scroll :rows-per-page-options="[0]" :visible-columns="visibleColumns"
      @row-click="onRowClick" :hide-bottom="true">
      <!--
      <template v-slot:top>
        <q-space></q-space>
        <q-select v-model="visibleColumns" multiple outlined dense options-dense :display-value="$q.lang.table.columns"
          emit-value map-options :options="columns" option-value="name" options-cover style="min-width: 150px" />
      </template>
      -->
      <template v-slot:body-cell-index="props">
        <q-td :props="props">
          <q-icon :name="rowIcon" color="pink" size="sm" class="q-mr-sm" v-if="currentTrackIndex + 1 == props.value"></q-icon>
          {{ props.value }} / {{ rows.length }}
        </q-td>
      </template>
      <template v-slot:body-cell-artist="props">
        <q-td :props="props">
          <router-link v-if="props.value" :class="{ 'text-white text-bold': false }"
            :to="{ name: 'artist', params: { name: props.value } }"><q-icon name="link" class="q-mr-sm"></q-icon>{{
              props.value }}</router-link>
        </q-td>
      </template>
      <template v-slot:body-cell-albumArtist="props">
        <q-td :props="props">
          <router-link v-if="props.value" :class="{ 'text-white text-bold': false }"
            :to="{ name: 'artist', params: { name: props.value } }"><q-icon name="link" class="q-mr-sm"></q-icon>{{
              props.value }}</router-link>
        </q-td>
      </template>
      <template v-slot:body-cell-actions="props">
        <q-td :props="props">
          {{ props.value }}
          <q-btn-group outline>
            <q-btn size="sm" color="white" text-color="grey-5" icon="north" :title="t('Up')" disable
              @click="onMoveUpTrackAtIndex" />
            <q-btn size="sm" color="white" text-color="grey-5" icon="south" :title="t('Down')" disable
              @click="onMoveDownTrackAtIndex" />
            <q-btn size="sm" color="white" text-color="grey-5" icon="favorite" :title="t('Toggle favorite')" disable />
            <q-btn size="sm" color="white" text-color="grey-5" icon="delete" :title="t('Remove')" disable
              @click="onRemoveElementAtIndex" />
          </q-btn-group>
        </q-td>
      </template>
    </q-table>

  </q-card>
  <q-dialog v-model="showSavePlaylistDialog">
    <q-card style="min-width: 350px">
      <q-card-section>
        <div class="text-h6">Save current playlist</div>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <q-input outlined dense v-model="newPlaylistName" autofocus @keyup.enter="showSavePlaylistDialog = false"
          label="Playlist name" />
      </q-card-section>

      <q-card-section class="q-pt-none">
        <q-toggle label="Public" color="pink" v-model="newPlaylistPublic" />
      </q-card-section>

      <q-card-actions align="right" class="">
        <q-btn outline label="Cancel" v-close-popup />
        <q-btn outline label="Save" :disable="!newPlaylistName" @click="onSavePlaylistElements" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<style lang="sass">
.my-sticky-header-table
  /* height or max-height is important */
  height: 310px

  .q-table__top,
  .q-table__bottom,
  thead tr:first-child th
    /* bg color is important for th; just specify one */
    background-color: #eeeeee

  thead tr th
    position: sticky
    z-index: 1
  thead tr:first-child th
    top: 0

  /* this is when the loading indicator appears */
  &.q-table--loading thead tr:last-child th
    /* height of all previous header rows */
    top: 48px

  /* prevent scrolling behind sticky top row on focus */
  tbody
    /* height of all previous header rows */
    scroll-margin-top: 48px
</style>

<script setup>
import { ref, watch, computed } from "vue";
import { useQuasar, uid } from "quasar";
import { useI18n } from 'vue-i18n';
import { api } from 'boot/axios';
import { usePlayer } from 'stores/player';
import { usePlayerStatusStore } from 'stores/playerStatus';
import { useCurrentPlaylistStore } from 'stores/currentPlaylist';
//import { default as CurrentPlaylistTableRow } from 'components/CurrentPlaylistTableRow.vue';

import { trackActions } from '../boot/spieldose';

const $q = useQuasar();

const { t } = useI18n();

const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();

const currentPlayListElementsLastChanges = computed(() => {
  return (currentPlaylist.getElementsLastChangeTimestamp);
});

const rows = ref([]);
const columns = [
  {
    name: 'index',
    required: true,
    label: 'Índice',
    align: 'right',
    field: row => row.index,
    sortable: false
  },
  {
    name: 'title',
    required: true,
    label: 'Title',
    align: 'left',
    field: row => row.title,
    sortable: true
  },
  {
    name: 'artist',
    required: true,
    label: 'Artist',
    align: 'left',
    field: row => row.artist.name,
    sortable: true
  },
  {
    name: 'albumArtist',
    required: true,
    label: 'Album artist',
    align: 'left',
    field: row => row.album.artist.name,
    sortable: true
  },
  {
    name: 'albumTitle',
    required: true,
    label: 'Album',
    align: 'left',
    field: row => row.album.title,
    sortable: true
  },
  {
    name: 'albumTrackIndex',
    required: true,
    label: 'Album Track nº',
    align: 'right',
    field: row => row.trackNumber,
    sortable: true
  },
  {
    name: 'year',
    required: true,
    label: 'Year',
    align: 'right',
    field: row => row.album.year,
    sortable: true
  },
  {
    name: 'actions',
    required: false,
    label: 'Actions',
    align: 'center',
    field: row => {
      index: row.index
    }
  },
];

const visibleColumns = ref(columns.map((column) => { return (column.name); }));

const initialPagination = ref({
  //sortBy: 'desc',
  descending: false,
  page: 1,
  rowsPerPage: 64
  // rowsNumber: xx if getting data from a server
});

const rowIcon = computed(() => {
  if (playerStatus.isPlaying) {
    return ('play_arrow');
  } else if (playerStatus.isPaused) {
    return ('pause');
  } else if (playerStatus.isStopped) {
    return ('stop');
  } else {
    return ('play_arrow');
  }
});

watch(currentPlayListElementsLastChanges, (newValue) => {
  elements.value = currentPlaylist.getElements;
  rows.value = elements.value.map((element, index) => { element.track.index = index + 1; return (element.track) });
  currentTrackIndex.value = currentPlaylist.getCurrentIndex;
});

const playerStatus = usePlayerStatusStore();

const elements = ref([]);

const currentTrackIndex = ref(0);

const loading = ref(false);

const showSavePlaylistDialog = ref(false);

const newPlaylistName = ref(null);

const newPlaylistPublic = ref(false);

function onClear() {
  player.stop();
  elements.value = [];
  rows.value = [];
  currentPlaylist.clear();
}

function setCurrentTrackIndex(index) {
  player.interact();
  currentPlaylist.saveCurrentTrackIndex(index);
  if (!playerStatus.isPlaying) {
    player.play();
  }
}


function onMoveUpTrackAtIndex(index) {
  // https://stackoverflow.com/a/6470794
  const element = elements.value[index];
  elements.value.splice(index, 1);
  elements.value.splice(index - 1, 0, element);
}

function onMoveDownTrackAtIndex(index) {
  // https://stackoverflow.com/a/6470794
  const element = elements.value[index];
  elements.value.splice(index, 1);
  elements.value.splice(index + 1, 0, element);
}

function onToggleFavoriteAtIndex(index) {
  const currentElement = elements.value[index];
  if (currentElement) {
    //loading.value = true;
    const funct = currentElement.favorited ? api.track.unSetFavorite : api.track.setFavorite;
    funct(currentElement.id).then((success) => {
      currentElement.favorited = success.data.favorited;
      currentPlaylist.saveElements(elements.value);
      // TODO use store
      //loading.value = false;
    })
      .catch((error) => {
        //loading.value = false;
        switch (error.response.status) {
          default:
            // TODO: custom error
            $q.notify({
              type: "negative",
              message: t("API Error: fatal error"),
              caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
            });
            break;
        }
      });
  }
}


function onRemoveElementAtIndex(index) {
  elements.value.splice(index, 1);
  currentPlaylist.saveElements(elements.value);
}

function onRowClick(evt, row, index) {
  player.interact();
  currentPlaylist.saveCurrentTrackIndex(index);
  if (!playerStatus.isPlaying) {
    player.play();
  }
}

function search() {
  player.interact();
  loading.value = true;
  currentTrackIndex.value = 0;
  api.track.search({}, 1, 32, true, null, null).then((success) => {
    elements.value = success.data.data.items.map((item) => { return ({ track: item }); });
    rows.value = elements.value.map((element, index) => { element.track.index = index + 1; return (element.track) });
    trackActions.play(elements.value);
    //currentPlaylist.saveElements(elements.value);
    loading.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: t("API Error: error loading random tracks"),
      caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
    });
    loading.value = false;
  });
}

const currentPlaylistTrackIndex = computed(() => {
  return (currentPlaylist.getCurrentIndex);
});

watch(currentPlaylistTrackIndex, (newValue) => {
  currentTrackIndex.value = newValue;
});

function onRandom() {
  player.stop();
  search();
}

function onPreviusPlaylist() {
  player.interact();
  currentPlaylist.skipPrevious();
}

function onPlay() {
  player.interact();
  player.play();

}

function onPause() {
  player.interact();
  player.play();
}

function onResume() {
  player.interact();
  player.play();
}

function onStop() {
  player.stop();
  player.setCurrentTime(0);
}

function onNextPlaylist() {
  player.interact();
  currentPlaylist.skipNext();
}

function onSavePlaylist() {
  newPlaylistName.value = null;
  showSavePlaylistDialog.value = true;
}

function onSavePlaylistElements() {
  player.interact();
  loading.value = true;
  currentTrackIndex.value = 0;
  api.playlist.add(uid(), newPlaylistName.value, elements.value.filter((element) => element.track).map((element) => { return (element.track.id); }), newPlaylistPublic.value).then((success) => {
    //elements.value = success.data.data.items.map((item) => { return ({ track: item }); });
    //currentPlaylist.saveElements(elements.value);
    loading.value = false;
    showSavePlaylistDialog.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: t("API Error: error loading random tracks"),
      caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
    });
    loading.value = false;
  });
}

elements.value = currentPlaylist.getElements;
rows.value = elements.value.map((element, index) => { element.track.index = index + 1; return (element.track) });
currentTrackIndex.value = currentPlaylist.getCurrentIndex;

</script>
