<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <q-breadcrumbs-el icon="list_alt" :label="t('Current playlist')" />
      <q-breadcrumbs-el v-if="spieldoseStore.getCurrentPlaylistLinkedPlaylist"
        :label="spieldoseStore.getCurrentPlaylistLinkedPlaylist.name" />
    </q-breadcrumbs>
    <q-btn-group spread class="q-mb-md">
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Clear') : ''" icon="clear" @click="onClear"
        :disable="loading || !(tableRows?.length > 0)">
      </q-btn>
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Discover') : ''" icon="bolt" @click="onDiscover"
        :disable="loading">
      </q-btn>
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Randomize') : ''" icon="shuffle"
        @click="onRandomizeSorting" :disable="loading || !(tableRows?.length > 0)">
      </q-btn>
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Previous') : ''" icon="skip_previous"
        @click="onPreviusPlaylist" :disable="loading || !spieldoseStore.allowSkipPrevious" />
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Play') : ''" icon="play_arrow" @click="onPlay"
        :disable="loading || !spieldoseStore.hasCurrentPlaylistElements" v-if="spieldoseStore.isStopped" />
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Pause') : ''" icon="pause" @click="onPause"
        :disable="loading || !(tableRows?.length > 0)" v-else-if="spieldoseStore.isPlaying" />
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Resume') : ''" icon="play_arrow"
        @click="onResume" :disable="loading || !(tableRows?.length > 0)" v-else-if="spieldoseStore.isPaused" />
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Stop') : ''" icon="stop" @click="onStop"
        :disable="loading || spieldoseStore.isStopped || !(tableRows?.length > 0)" />
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Next') : ''" icon="skip_next"
        @click="onNextPlaylist" :disable="loading || !spieldoseStore.allowSkipNext" />
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Download') : ''" icon="save_alt"
        :disable="loading || !spieldoseStore.isCurrentPlaylistElementATrack" :href="spieldoseStore.isCurrentPlaylistElementATrack ? spieldoseStore.getCurrentPlaylistElementURL: '#'" />
      <q-btn size="md" outline color="dark" :label="$q.screen.gt.md ? t('Save as') : ''" icon="save_alt"
        :disable="loading || !(tableRows?.length > 0)" @click="onSavePlaylist" />
    </q-btn-group>
    <q-table ref="tableRef" class="my-sticky-header-table" style="height: 46.2em" :rows="tableRows" :columns="tableColumns"
      row-key="id" virtual-scroll :rows-per-page-options="[0]" :hide-bottom="true">
      <template v-slot:body="props">
        <q-tr class="cursor-pointer" :props="props" @click="(evt) => onRowClick(evt, props.row, props.row.index - 1)"
          :class="{ 'selected-row': currentTrackIndex + 1 == props.row.index }">
          <q-td key="index" :props="props">
            <q-icon :name="currentElementRowIcon" color="pink" size="sm" class="q-mr-sm"
              v-if="currentTrackIndex + 1 == props.row.index"></q-icon>
            {{ props.row.index }} / {{ tableRows.length }}
          </q-td>
          <q-td key="title" :props="props">
            {{ props.row.title }}
          </q-td>
          <q-td key="artist" :props="props">
            <router-link v-if="props.row.artist.name" :class="{ 'text-white text-bold': false }"
              :to="{ name: 'artist', params: { name: props.row.artist.name }, query: { mbid: props.row.artist.mbId, tab: 'overview' } }"><q-icon
                name="link" class="q-mr-sm"></q-icon>{{
                  props.row.artist.name }}</router-link>
          </q-td>
          <q-td key="albumArtist" :props="props">
            <router-link v-if="props.row.album.artist.name" :class="{ 'text-white text-bold': false }"
              :to="{ name: 'artist', params: { name: props.row.album.artist.name }, query: { mbid: props.row.album.artist.mbId, tab: 'overview' } }"><q-icon
                name="link" class="q-mr-sm"></q-icon>{{ props.row.album.artist.name }}</router-link>
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
              <q-btn size="sm" color="white" text-color="grey-5" icon="north" :title="t('Up')"
                :disable="props.row.index == 1 || props.row.id == currentTrackIndex"
                @click="onMoveUpTrackAtIndex(props.row.index - 1)" />
              <q-btn size="sm" color="white" text-color="grey-5" icon="south" :title="t('Down')"
                :disable="props.row.index == tableRows.length || props.row.id == currentTrackIndex"
                @click="onMoveDownTrackAtIndex(props.row.index - 1)" />
                <q-btn size="sm" color="white" text-color="grey-5" icon="delete" :title="t('Remove')"
                @click.stop.prevent="onRemoveElementAtIndex(props.row.index - 1)" />
              <q-btn size="sm" color="white" :text-color="props.row.favorited ? 'pink' : 'grey-5'" icon="favorite"
                :title="t('Toggle favorite')"
                @click="onToggleFavorite(props.row.index, props.row.id, props.row.favorited)" />
              <q-btn size="sm" color="white" text-color="grey-5" icon="save_alt" :title="t('Download')" :href="props.row.url"/>

            </q-btn-group>
          </q-td>
        </q-tr>
      </template>
    </q-table>
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
  </q-card>
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
import { ref, watch, computed, onMounted, inject } from "vue";
import { useQuasar, uid } from "quasar";
import { useI18n } from 'vue-i18n';
import { api } from 'boot/axios';
//import { default as CurrentPlaylistTableRow } from 'components/CurrentPlaylistTableRow.vue';
import { spieldoseEventNames } from "boot/events";
import { useSpieldoseStore } from "stores/spieldose";

import { trackActions, playListActions, currentPlayListActions } from '../boot/spieldose';

const $q = useQuasar();
const { t } = useI18n();

const spieldoseStore = useSpieldoseStore();

const tableRef = ref(null);

const tableRows = ref([]);
const tableColumns = [
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
    sortable: false
  },
  {
    name: 'artist',
    required: true,
    label: 'Artist',
    align: 'left',
    field: row => row.artist.name,
    sortable: false
  },
  {
    name: 'albumArtist',
    required: false,
    label: 'Album artist',
    align: 'left',
    field: row => row.album.artist.name,
    sortable: false
  },
  {
    name: 'albumTitle',
    required: false,
    label: 'Album',
    align: 'left',
    field: row => row.album.title,
    sortable: false
  },
  {
    name: 'albumTrackIndex',
    required: false,
    label: 'Album Track nº',
    align: 'right',
    field: row => row.trackNumber,
    sortable: false
  },
  {
    name: 'year',
    required: false,
    label: 'Year',
    align: 'right',
    field: row => row.album.year,
    sortable: false
  },
  {
    name: 'actions',
    required: true,
    label: 'Actions',
    align: 'center',
    favorited: row => row.favorited
  },
];

const currentElementRowIcon = computed(() => {
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

const currentTrackIndex = ref(0);
let shuffledIndexes = [];

const loading = ref(false);

const showSavePlaylistDialog = ref(false);

const newPlaylistName = ref(null);

const newPlaylistPublic = ref(false);


const bus = inject('bus');

bus.on(spieldoseEventNames.track.setFavorite, (data) => {
  if (data.source != "CurrentPlaylistPage" && tableRows?.value.length > 0) {
    const index = tableRows.value.findIndex(
      (element) => element && element.id == data.id
    );
    if (index !== -1) {
      tableRows.value[index].favorited = data.timestamp;
    }
  }
});

bus.on(spieldoseEventNames.track.unSetFavorite, (data) => {
  if (data.source != "CurrentPlaylistPage" && tableRows?.value.length > 0) {
    const index = tableRows.value.findIndex(
      (element) => element && element.id == data.id
    );
    if (index !== -1) {
      tableRows.value[index].favorited = null;
    }
  }
});

function onClear() {
  spieldoseStore.interact();
  spieldoseStore.stop();
  currentPlayListActions.clear();
  tableRows.value = [];
}

function onMoveUpTrackAtIndex(oldIndex) {
  let indexes = Array.from({ length: tableRows.value.length }, (e, i) => i);
  // https://stackoverflow.com/a/6470794
  indexes.splice(oldIndex, 1);
  indexes.splice(oldIndex - 1, 0, oldIndex);
  currentPlayListActions.resortByIndexes(indexes).then((success) => {
    tableRows.value = success.data.tracks.map((element, index) => { element.index = index + 1; return (element) });
    currentTrackIndex.value = currentPlaylistTrackIndex.value;
    loading.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: t("API Error: error resorting tracks"),
      caption: t("API Error: fatal error details", {
        status: error && error.response ? error.response.status : 'undefined', statusText: error && error.response
          ? error.response.statusText : 'undefined'
      })
    });
    loading.value = false;
  });
}

function onMoveDownTrackAtIndex(oldIndex) {
  let indexes = Array.from({ length: tableRows.value.length }, (e, i) => i);
  // https://stackoverflow.com/a/6470794
  indexes.splice(oldIndex, 1);
  indexes.splice(oldIndex + 1, 0, oldIndex);
  currentPlayListActions.resortByIndexes(indexes).then((success) => {
    tableRows.value = success.data.tracks.map((element, index) => { element.index = index + 1; return (element) });
    currentTrackIndex.value = currentPlaylistTrackIndex.value;
    loading.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: t("API Error: error resorting tracks"),
      caption: t("API Error: fatal error details", {
        status: error && error.response ? error.response.status : 'undefined', statusText: error && error.response
          ? error.response.statusText : 'undefined'
      })
    });
    loading.value = false;
  });
}

function onToggleFavorite(index, trackId, favorited) {
  const funct = !favorited ? trackActions.setFavorite : trackActions.unSetFavorite;
  funct(trackId, 'CurrentPlaylistPage').then((success) => {
    tableRows.value[index - 1].favorited = success.data.favorited;
  })
    .catch((error) => {
      switch (error.response.status) {
        default:
          $q.notify({
            type: "negative",
            message: t("API Error: error toggling favorite flag"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
    });
}

function onRemoveElementAtIndex(index) {
  loading.value = true;
  currentPlayListActions.removeElementAtIndex(index).then((success) => {
    tableRows.value = success.data.tracks.map((element, index) => { element.index = index + 1; return (element) });
    currentTrackIndex.value = currentPlaylistTrackIndex.value;
    loading.value = false;
  })
    .catch((error) => {
      switch (error.response.status) {
        default:
          $q.notify({
            type: "negative",
            message: t("API Error: error removing element"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
      loading.value = false;
    });
}

function onRowClick(evt, row, index) {
  if (evt.target.nodeName != 'A' && evt.target.nodeName != 'I' && evt.target.nodeName != 'BUTTON') { // PREVENT play if we are clicking on action buttons
    spieldoseStore.interact();
    currentPlayListActions.skipToElementIndex(index).then((success) => {
    }).catch((error) => {
      // TODO
    });
  }
}

function getCurrentPlaylist() {
  loading.value = true;
  currentPlayListActions.get().then((success) => {
    tableRows.value = success.data.tracks.map((element, index) => { element.index = index + 1; return (element) });
    shuffledIndexes.value = success.data.shuffledIndexes;
    currentTrackIndex.value = !spieldoseStore.getShuffle ? success.data.currentIndex : shuffledIndexes.value[success.data.currentIndex];
    loading.value = false;
  }).catch((error) => {
    // TODO
    loading.value = false;
  });
}

const currentPlaylistTrackIndex = computed(() => {
  if (!spieldoseStore.getShuffle) {
    return (spieldoseStore.getCurrentPlaylistIndex);
  } else {
    return (spieldoseStore.getCurrentPlaylistShuffledIndex);
  }
});

watch(currentPlaylistTrackIndex, (newValue) => {
  currentTrackIndex.value = newValue;
  tableRef.value.scrollTo(newValue, 'center-force');
});

function onRandomizeSorting() {
  spieldoseStore.stop();
  loading.value = true;
  currentPlayListActions.randomize().then((success) => {
    tableRows.value = success.data.tracks.map((element, index) => { element.index = index + 1; return (element) });
    currentTrackIndex.value = currentPlaylistTrackIndex.value;
    loading.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: t("API Error: error loading random tracks"),
      caption: t("API Error: fatal error details", {
        status: error && error.response ? error.response.status : 'undefined', statusText: error && error.response
          ? error.response.statusText : 'undefined'
      })
    });
    loading.value = false;
  });
}

function onDiscover() {
  spieldoseStore.stop();
  loading.value = true;
  currentPlayListActions.discover(32).then((success) => {
    tableRows.value = success.data.tracks.map((element, index) => { element.index = index + 1; return (element) });
    currentTrackIndex.value = currentPlaylistTrackIndex.value;
    loading.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: t("API Error: error loading random tracks"),
      caption: t("API Error: fatal error details", {
        status: error && error.response ? error.response.status : 'undefined', statusText: error && error.response
          ? error.response.statusText : 'undefined'
      })
    });
    loading.value = false;
  });
}

function onPreviusPlaylist() {
  spieldoseStore.interact();
  currentPlayListActions.skipToPreviousElement().then((success) => {
  }).catch((error) => {
    // TODO
  });
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
  currentPlayListActions.skipToNextElement().then((success) => {
  }).catch((error) => {
    // TODO
  });
}

function onSavePlaylist() {
  newPlaylistName.value = null;
  if (spieldoseStore.getCurrentPlaylistLinkedPlaylist) {
    newPlaylistName.value = spieldoseStore.getCurrentPlaylistLinkedPlaylist.name;
  }
  showSavePlaylistDialog.value = true;
}

function onSavePlaylistElements() {
  const ids = tableRows.value.map((element) => element.id);
  spieldoseStore.interact();
  loading.value = true;
  const funct = spieldoseStore.getCurrentPlaylistLinkedPlaylist ? api.playlist.update: api.playlist.add;
  const id = spieldoseStore.getCurrentPlaylistLinkedPlaylist ? spieldoseStore.getCurrentPlaylistLinkedPlaylist.id: uid();
  funct(id, newPlaylistName.value, ids, newPlaylistPublic.value).then((success) => {
    spieldoseStore.data.currentPlaylist.playlist = {
      id: id,
      name: newPlaylistName.value
    };
    loading.value = false;
    showSavePlaylistDialog.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: t("API Error: error loading random tracks"),
      caption: t("API Error: fatal error details", {
        status: error && error.response ? error.response.status : 'undefined', statusText: error && error.response
          ? error.response.statusText : 'undefined'
      })
    });
    loading.value = false;
  });
}

currentTrackIndex.value = currentPlaylistTrackIndex.value;

onMounted(() => {
  getCurrentPlaylist();
  if (currentTrackIndex.value > 0) {
    tableRef.value.scrollTo(currentTrackIndex.value, 'center-force');
  }
});


</script>
