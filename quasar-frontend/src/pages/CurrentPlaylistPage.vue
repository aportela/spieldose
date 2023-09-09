<template>
  <q-page>
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
      <q-markup-table flat bordered>
        <thead>
          <tr class="bg-grey-2 text-grey-10">
            <th class="text-right">{{ t('Index') }}</th>
            <th class="text-left">{{ t('Title') }}</th>
            <th class="text-left">{{ t('Artist') }}</th>
            <th class="text-left gt-lg">{{ t('Album Artist') }}</th>
            <th class="text-left">{{ t('Album') }}</th>
            <th class="text-right gt-lg">{{ t('Album Track nº') }}</th>
            <th class="text-right">{{ t('Year') }}</th>
            <th class="text-center">{{ t('Actions') }}</th>
          </tr>
        </thead>
        <tbody v-if="!loading">
          <CurrentPlaylistTableRow v-for="track, index in elements" :key="track.id" :element="track" :index="index"
            :lastIndex="elements.length" :selected="currentTrackIndex == index"
            @setcurrentIndex="setCurrentTrackIndex(index)" @up="onMoveUpTrackAtIndex(index)"
            @down="onMoveDownTrackAtIndex(index)" @toggleFavorite="onToggleFavoriteAtIndex(index)"
            @remove="onRemoveElementAtIndex(index)" :disabled="loading" :isPlaying="playerStatus.isPlaying"
            :isPaused="playerStatus.isPaused" :isStopped="playerStatus.isStopped">
          </CurrentPlaylistTableRow>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="8" class="text-center">
              <q-spinner v-show="loading" color="pink" size="xl" class="q-ml-sm" :thickness="10" />
            </td>
          </tr>
        </tbody>
      </q-markup-table>
    </q-card>
    <q-dialog v-model="showSavePlaylistDialog">
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">Save current playlist</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-input outlined dense v-model="newPlaylistName" autofocus @keyup.enter="showSavePlaylistDialog = false" label="Playlist name"/>
        </q-card-section>

        <q-card-actions align="right" class="">
          <q-btn outline label="Cancel" v-close-popup />
          <q-btn outline label="Save" :disable="! newPlaylistName" @click="onSavePlaylistElements"/>
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { useQuasar, uid } from "quasar";
import { useI18n } from 'vue-i18n';
import { api } from 'boot/axios';
import { usePlayer } from 'stores/player';
import { usePlayerStatusStore } from 'stores/playerStatus';
import { useCurrentPlaylistStore } from 'stores/currentPlaylist';
import { default as CurrentPlaylistTableRow } from 'components/CurrentPlaylistTableRow.vue';

const $q = useQuasar();

const { t } = useI18n();

const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();

const currentPlayListElementsLastChanges = computed(() => {
  return (currentPlaylist.getElementsLastChangeTimestamp);
});

watch(currentPlayListElementsLastChanges, (newValue) => {
  elements.value = currentPlaylist.getElements;
  currentTrackIndex.value = currentPlaylist.getCurrentIndex;

});

const playerStatus = usePlayerStatusStore();

const elements = ref([]);

const currentTrackIndex = ref(0);

const loading = ref(false);

const showSavePlaylistDialog = ref(false);

const newPlaylistName = ref(null);

function onClear() {
  player.stop();
  elements.value = [];
  currentPlaylist.saveElements([]);
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

function search() {
  player.interact();
  loading.value = true;
  currentTrackIndex.value = 0;
  api.track.search({}, 1, 32, true, null, null).then((success) => {
    elements.value = success.data.data.items.map((item) => { return ({ track: item }); });
    currentPlaylist.saveElements(elements.value);
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
  api.playlist.add(uid(), newPlaylistName.value, elements.value.filter((element) => element.track).map((element) => {return(element.track.id); }) ).then((success) => {
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
currentTrackIndex.value = currentPlaylist.getCurrentIndex;

</script>
