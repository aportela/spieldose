<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <q-breadcrumbs-el icon="list_alt" :label="t('Current playlist')" />
    </q-breadcrumbs>
    <q-btn-group spread class="q-mb-md">
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Clear') : ''" icon="clear" @click="onClear"
        :disable="loading || !(elements && elements.length > 0)">
      </q-btn>
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Randomize') : ''" icon="bolt" @click="onRandom"
        :disable="loading">
      </q-btn>
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Previous') : ''" icon="skip_previous"
        @click="onPreviusPlaylist" :disable="loading || !spieldoseStore.allowSkipPrevious" />
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Play') : ''" icon="play_arrow" @click="onPlay"
        :disable="loading || !spieldoseStore.hasCurrentPlaylistElements" v-if="spieldoseStore.isStopped" />
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Pause') : ''" icon="pause" @click="onPause"
        :disable="loading || !(elements && elements.length > 0)" v-else-if="spieldoseStore.isPlaying" />
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Resume') : ''" icon="play_arrow"
        @click="onResume" :disable="loading || !(elements && elements.length > 0)" v-else-if="spieldoseStore.isPaused" />
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Stop') : ''" icon="stop" @click="onStop"
        :disable="loading || spieldoseStore.isStopped || !(elements && elements.length > 0)" />
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Next') : ''" icon="skip_next"
        @click="onNextPlaylist" :disable="loading || !spieldoseStore.allowSkipNext" />
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Download') : ''" icon="save_alt"
        :disable="loading || !spieldoseStore.getCurrentElementURL" :href="spieldoseStore.getCurrentElementURL" />
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Save as') : ''" icon="save_alt"
        :disable="loading || !(elements && elements.length > 0)" @click="onSavePlaylist" />
    </q-btn-group>
    <q-table ref="tableRef" class="my-sticky-header-table" style="height: 46.8em" title="Current playlist" :rows="rows"
      :columns="columns" row-key="id" virtual-scroll :rows-per-page-options="[0]" :visible-columns="visibleColumns"
      :hide-bottom="true">
      <!--
      <template v-slot:top>
        <q-space></q-space>
        <q-select v-model="visibleColumns" multiple outlined dense options-dense :display-value="$q.lang.table.columns"
          emit-value map-options :options="columns" option-value="name" options-cover style="min-width: 150px" />
      </template>
      -->
      <template v-slot:body="props">
        <q-tr class="cursor-pointer" :props="props" @click="(evt) => onRowClick(evt, props.row, props.row.index - 1)"
          :class="{ 'selected-row': currentTrackIndex + 1 == props.row.index }">
          <q-td key="index" :props="props">
            <q-icon :name="rowIcon" color="pink" size="sm" class="q-mr-sm"
              v-if="currentTrackIndex + 1 == props.row.index"></q-icon>
            {{ props.row.index }} / {{ rows.length }}
          </q-td>
          <q-td key="title" :props="props">
            {{ props.row.title }}
          </q-td>
          <q-td key="artist" :props="props">
            <router-link v-if="props.row.artist.name" :class="{ 'text-white text-bold': false }"
              :to="{ name: 'artist', params: { name: props.row.artist.name } }"><q-icon name="link"
                class="q-mr-sm"></q-icon>{{
                  props.row.artist.name }}</router-link>
          </q-td>
          <q-td key="albumArtist" :props="props">
            <router-link v-if="props.row.album.artist.name" :class="{ 'text-white text-bold': false }"
              :to="{ name: 'artist', params: { name: props.row.album.artist.name } }"><q-icon name="link"
                class="q-mr-sm"></q-icon>{{ props.row.album.artist.name }}</router-link>
          </q-td>
          <q-td key="albumTitle" :props="props">
            {{ props.row.album.title }}
          </q-td>
          <q-td key="albumTrackIndex" :props="props">
            {{ props.row.trackNumber }}
          </q-td>
          <q-td key="year" :props="props">
            {{ props.row.album.year }}
          </q-td>
          <q-td key="actions" :props="props">
            <q-btn-group outline>
              <q-btn size="sm" color="white" text-color="grey-5" icon="north" :title="t('Up')" disable
                @click="onMoveUpTrackAtIndex" />
              <q-btn size="sm" color="white" text-color="grey-5" icon="south" :title="t('Down')" disable
                @click="onMoveDownTrackAtIndex" />
              <q-btn size="sm" color="white" :text-color="props.row.favorited ? 'pink' : 'grey-5'" icon="favorite"
                :title="t('Toggle favorite')" @click="onToggleFavorite(props.row.id, props.row.favorited)" />
              <q-btn size="sm" color="white" text-color="grey-5" icon="delete" :title="t('Remove')"
                @click.stop.prevent="onRemoveElementAtIndex(props.row.index - 1)" />
            </q-btn-group>
          </q-td>
        </q-tr>
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

    tr.selected-row
      background: #f0cbd1 !important
      color: #222
    tr
      td
        a
          text-decoration: none


</style>

<script setup>
import { ref, watch, computed } from "vue";
import { useQuasar, uid } from "quasar";
import { useI18n } from 'vue-i18n';
import { api } from 'boot/axios';
//import { default as CurrentPlaylistTableRow } from 'components/CurrentPlaylistTableRow.vue';
import { useSpieldoseStore } from "stores/spieldose";

import { trackActions, playListActions } from '../boot/spieldose';

const $q = useQuasar();

const { t } = useI18n();

const spieldoseStore = useSpieldoseStore();



const currentPlayListElementsLastChanges = computed(() => {
  return (spieldoseStore.getCurrentPlaylistLastChangedTimestamp);
});

const tableRef = ref(null);

const rows = ref([]);
const columns = [
  {
    name: 'index',
    required: true,
    label: 'Index',
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
    required: false,
    label: 'Album artist',
    align: 'left',
    field: row => row.album.artist.name,
    sortable: true
  },
  {
    name: 'albumTitle',
    required: false,
    label: 'Album',
    align: 'left',
    field: row => row.album.title,
    sortable: true
  },
  {
    name: 'albumTrackIndex',
    required: false,
    label: 'Album Track nº',
    align: 'right',
    field: row => row.trackNumber,
    sortable: true
  },
  {
    name: 'year',
    required: false,
    label: 'Year',
    align: 'right',
    field: row => row.album.year,
    sortable: true
  },
  {
    name: 'actions',
    required: true,
    label: 'Actions',
    align: 'center',
    favorited: row => row.favorited
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
  if (spieldoseStore.isPlaying) {
    return ('play_arrow');
  } else if (spieldoseStore.isPaused) {
    return ('pause');
  } else if (spieldoseStore.isStopped) {
    return ('stop');
  } else {
    return ('play_arrow');
  }
});

watch(currentPlayListElementsLastChanges, (newValue) => {
  elements.value = spieldoseStore.getCurrentPlaylist.elements;
  rows.value = elements.value.map((element, index) => { element.track.index = index + 1; return (element.track) });
  currentTrackIndex.value = spieldoseStore.getCurrentPlaylistIndex;
});

const elements = ref([]);

const currentTrackIndex = ref(0);

const loading = ref(false);

const showSavePlaylistDialog = ref(false);

const newPlaylistName = ref(null);

const newPlaylistPublic = ref(false);

function onClear() {
  spieldoseStore.clearCurrentPlaylist();
  // TODO: required ? not sure
  //elements.value = [];
  //rows.value = [];
}

function setCurrentTrackIndex(index) {
  spieldoseStore.interact();
  spieldoseStore.skipToIndex(index);
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

function onToggleFavorite(trackId, favorited) {
  const funct = !favorited ? trackActions.setFavorite : trackActions.unSetFavorite;
  funct(trackId).then((success) => {
  })
    .catch((error) => {
      switch (error.response.status) {
        default:
          // TODO: custom message
          $q.notify({
            type: "negative",
            message: t("API Error: error when toggling favorite flag"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
    });
}

function onRemoveElementAtIndex(index) {
  // TODO
  //elements.value.splice(index, 1);
  //rows.value = elements.value.map((element, index) => { element.track.index = index + 1; return (element.track) });
  //spieldoseStore.saveCurrentPlaylistElements(elements.value);
}

function onRowClick(evt, row, index) {
  if (evt.target.nodeName != 'I' && evt.target.nodeName != 'BUTTON') { // PREVENT play if we are clicking on action buttons
    spieldoseStore.interact();
    spieldoseStore.skipToIndex(index);
  }
}

function search() {
  spieldoseStore.interact();
  loading.value = true;
  currentTrackIndex.value = 0;
  api.track.search({}, 1, 32, true, null, null).then((success) => {
    elements.value = success.data.data.items.map((item) => { return ({ track: item }); });
    rows.value = elements.value.map((element, index) => { element.track.index = index + 1; return (element.track) });
    //trackActions.play(elements.value);
    spieldoseStore.sendElementsToCurrentPlaylist(elements.value);
    //currentPlaylist.saveElements(elements.value);
    tableRef.value.scrollTo(0, 'center-force');
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
  return (spieldoseStore.getCurrentPlaylistIndex);
});

watch(currentPlaylistTrackIndex, (newValue) => {
  currentTrackIndex.value = newValue;
  tableRef.value.scrollTo(newValue, 'center-force');
});

function onRandom() {
  spieldoseStore.stop();
  search();
}

function onPreviusPlaylist() {
  spieldoseStore.interact();
  playListActions.skipPrevious();
}

function onPlay() {
  spieldoseStore.interact();
  spieldoseStore.play();

}

function onPause() {
  spieldoseStore.interact();
  spieldoseStore.play();
}

function onResume() {
  spieldoseStore.interact();
  spieldoseStore.play();
}

function onStop() {
  spieldoseStore.stop();
  spieldoseStore.setCurrentTime(0);
}

function onNextPlaylist() {
  spieldoseStore.interact();
  spieldoseStore.skipNext();
}

function onSavePlaylist() {
  newPlaylistName.value = null;
  showSavePlaylistDialog.value = true;
}

function onSavePlaylistElements() {
  spieldoseStore.interact();
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

elements.value = spieldoseStore.getCurrentPlaylist.elements;
rows.value = elements.value.map((element, index) => { element.track.index = index + 1; return (element.track) });
currentTrackIndex.value = spieldoseStore.getCurrentPlaylistIndex;

</script>
