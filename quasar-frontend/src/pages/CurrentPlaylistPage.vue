<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="list_alt" label="Current playlist" />
      </q-breadcrumbs>
      <q-btn-group spread class="q-mb-md">
        <q-btn outline color="dark" label="Clear" icon="clear" @click="onClear"
          :disable="loading || !(tracks && tracks.length > 0)">
        </q-btn>
        <q-btn outline color="dark" label="Random" icon="bolt" @click="onRandom" :disable="loading">
        </q-btn>
        <q-btn outline color="dark" label="Previous" icon="skip_previous" @click="onPreviusPlaylist"
          :disable="loading || !currentPlaylist.allowSkipPrevious" />
        <q-btn outline color="dark" label="Play" icon="play_arrow" @click="onPlay" :disable="loading || ! currentPlaylist.hasTracks"
          v-if="playerStatus.isStopped" />
        <q-btn outline color="dark" label="Pause" icon="pause" @click="onPause" :disable="loading"
          v-else-if="playerStatus.isPlaying" />
        <q-btn outline color="dark" label="Resume" icon="play_arrow" @click="onResume" :disable="loading"
          v-else-if="playerStatus.isPaused" />
        <q-btn outline color="dark" label="Stop" icon="stop" @click="onStop"
          :disable="loading || playerStatus.isStopped" />
        <q-btn outline color="dark" label="Next" icon="skip_next" @click="onNextPlaylist"
          :disable="loading || !currentPlaylist.allowSkipNext" />
        <q-btn outline color="dark" label="Download" icon="save_alt"
          :disable="loading || !currentPlaylist.getCurrentTrackURL" :href="currentPlaylist.getCurrentTrackURL" />
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
          <tr v-for="track, index in tracks" :key="track.id" class="non-selectable"
            :class="{ 'bg-pink text-white': currentTrackIndex == index }">
            <td class="text-right cursor-pointer" @click="setCurrentTrackIndex(index)"><q-icon name="play_arrow" size="sm" class="q-mr-sm"
                v-if="currentTrackIndex == index"></q-icon>{{ index + 1 }}/32</td>
            <td class="text-left cursor-pointer" @click="setCurrentTrackIndex(index)">{{ track.title }}</td>
            <td class="text-left"><router-link v-if="track.artist"
                :class="{ 'text-white text-bold': currentTrackIndex == index }"
                :to="{ name: 'artist', params: { name: track.artist } }"><q-icon name="link" class="q-mr-sm"></q-icon>{{
                  track.artist }}</router-link></td>
            <td class="text-left"><router-link v-if="track.albumArtist"
                :class="{ 'text-white text-bold': currentTrackIndex == index }"
                :to="{ name: 'artist', params: { name: track.albumArtist } }"><q-icon name="link"
                  class="q-mr-sm"></q-icon>{{ track.albumArtist }}</router-link></td>
            <td class="text-left">{{ track.album }}<span class="is-clickable"><i class="fas fa-link ml-1"></i></span></td>
            <td class="text-right">{{ track.trackNumber }}</td>
            <td class="text-right">{{ track.year }}</td>
            <td class="text-center">
              <q-btn-group outline>
                <q-btn size="sm" color="white" text-color="grey-5" icon="north" title="Up" disabled />
                <q-btn size="sm" color="white" text-color="grey-5" icon="south" title="Down" disabled />
                <q-btn size="sm" color="white" text-color="grey-5" icon="favorite" title="Toggle favorite" disabled />
                <q-btn size="sm" color="white" text-color="grey-5" icon="download" title="Download" :disable="loading"
                  :href="'api/2/file/' + track.id" />
              </q-btn-group>
            </td>
          </tr>
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

const $q = useQuasar();

const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();

const playerStatus = usePlayerStatusStore();

const tracks = ref([]);

const currentTrackIndex = ref(0);

const loading = ref(false);

function onClear() {
  player.stop();
  tracks.value = [];
  currentPlaylist.saveTracks([]);
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
    tracks.value = success.data.tracks;
    currentPlaylist.saveTracks(success.data.tracks);
    loading.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: "API Error: error loading tracks",
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
  if (currentTrackIndex.value < tracks.value.length - 1) {
    currentTrackIndex.value++;
  }
  */
}

tracks.value = currentPlaylist.getTracks;
currentTrackIndex.value = currentPlaylist.getCurrentIndex;

</script>
