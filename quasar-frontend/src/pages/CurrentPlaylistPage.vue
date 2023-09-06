<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="list_alt" label="Current playlist" />
      </q-breadcrumbs>
      <q-btn-group spread class="q-mb-md">
        <q-btn outline color="dark" label="Clear" icon="clear" @click="onClear"
          :disable="loading || !(elements && elements.length > 0)">
        </q-btn>
        <q-btn outline color="dark" label="Random" icon="bolt" @click="onRandom" :disable="loading">
        </q-btn>
        <q-btn outline color="dark" label="Previous" icon="skip_previous" @click="onPreviusPlaylist"
          :disable="loading || !currentPlaylist.allowSkipPrevious" />
        <q-btn outline color="dark" label="Play" icon="play_arrow" @click="onPlay"
          :disable="loading || !currentPlaylist.hasElements" v-if="playerStatus.isStopped" />
        <q-btn outline color="dark" label="Pause" icon="pause" @click="onPause" :disable="loading"
          v-else-if="playerStatus.isPlaying" />
        <q-btn outline color="dark" label="Resume" icon="play_arrow" @click="onResume" :disable="loading"
          v-else-if="playerStatus.isPaused" />
        <q-btn outline color="dark" label="Stop" icon="stop" @click="onStop"
          :disable="loading || playerStatus.isStopped" />
        <q-btn outline color="dark" label="Next" icon="skip_next" @click="onNextPlaylist"
          :disable="loading || !currentPlaylist.allowSkipNext" />
        <q-btn outline color="dark" label="Download" icon="save_alt"
          :disable="loading || !currentPlaylist.getCurrentElementURL" :href="currentPlaylist.getCurrentElementURL" />
      </q-btn-group>
      <q-markup-table flat bordered>
        <thead>
          <tr class="bg-grey-2 text-grey-10">
            <th class="text-right">Index</th>
            <th class="text-left">Title</th>
            <th class="text-left">Artist</th>
            <th class="text-left">Album Artist</th>
            <th class="text-left">Album</th>
            <th class="text-right">Album Track nº</th>
            <th class="text-right">Year</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody v-if="!loading">
          <CurrentPlaylistTableRow v-for="track, index in elements" :key="track.id" :element="track" :index="index" :selected="currentTrackIndex == index" @changeIndex="setCurrentTrackIndex(index)" :disabled="loading"></CurrentPlaylistTableRow>
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
  </q-page>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { useQuasar } from "quasar";
import { api } from 'boot/axios'
import { usePlayer } from 'stores/player';
import { usePlayerStatusStore } from 'stores/playerStatus'
import { useCurrentPlaylistStore } from 'stores/currentPlaylist'
import { default as CurrentPlaylistTableRow } from 'components/CurrentPlaylistTableRow.vue';

const $q = useQuasar();

const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();

const playerStatus = usePlayerStatusStore();

const elements = ref([]);

const currentTrackIndex = ref(0);

const loading = ref(false);

function onClear() {
  player.stop();
  elements.value = [];
  currentPlaylist.saveElements([]);
}

function setCurrentTrackIndex(index) {
  player.interact();
  currentPlaylist.saveCurrentTrackIndex(index);
}

function search() {
  player.interact();
  loading.value = true;
  currentTrackIndex.value = 0;
  api.track.search(1, 32, true, {}).then((success) => {
    elements.value = success.data.tracks.map((track) => { return({ track: track }); } );
    currentPlaylist.saveElements(elements.value);
    loading.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: "API Error: error loading elements",
      caption: "API Error: fatal error details: HTTP {" + error.response.status + "} ({" + error.response.statusText + "})"
    });
    loading.value = false;
  });
}

/*
watch(currentTrackIndex, (newValue, oldValue) => {
  currentPlaylist.saveCurrentTrackIndex(newValue);
});

*/
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
  /*
  if (currentTrackIndex.value > 0) {
    currentTrackIndex.value--;
  }
  */
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
  /*
  if (currentTrackIndex.value < elements.value.length - 1) {
    currentTrackIndex.value++;
  }
  */
}

elements.value = currentPlaylist.getElements;
currentTrackIndex.value = currentPlaylist.getCurrentIndex;

</script>
