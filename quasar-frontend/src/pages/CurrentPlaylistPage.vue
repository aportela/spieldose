<template>
  <q-page>
    <div class="q-pa-md">
      <q-btn-group spread class="q-mb-md">
        <q-btn outline color="dark" label="Clear" icon="clear" @click="clear" :disable="loading" />
        <q-btn outline color="dark" label="Random" icon="shuffle" @click="search" :disable="loading" />
        <q-btn outline color="dark" label="Previous" icon="skip_previous" @click="onPreviusPlaylist" :disable="loading" />
        <q-btn outline color="dark" label="Play" icon="play_arrow" :disable="loading" />
        <q-btn outline color="dark" label="Next" icon="skip_next" @click="onNextPlaylist" :disable="loading" />
        <q-btn outline color="dark" label="Download" icon="save_alt" :disable="loading" />
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
          </tr>
        </thead>
        <tbody>
          <tr v-for="track, index in tracks" :key="track.id" class="non-selectable cursor-pointer"
            :class="{ 'bg-pink text-white': currentTrackIndex == index }" @click="currentTrackIndex = index">
            <td class="text-right"><q-icon name="play_arrow" size="sm" class="q-mr-sm"
                v-if="currentTrackIndex == index"></q-icon>{{ index + 1 }}/32</td>
            <td class="text-left">{{ track.title }}</td>
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
          </tr>
        </tbody>
      </q-markup-table>
    </div>
  </q-page>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { default as leftSidebar } from 'components/AppLeftSidebar.vue';
import { api } from 'boot/axios'

import { useCurrentPlaylistStore } from 'stores/currentPlaylist'

const currentPlaylist = useCurrentPlaylistStore();

currentPlaylist.load();

const tracks = ref([]);

const currentTrackIndex = ref(0);

const loading = ref(false);

function clear() {
  tracks.value = [];
  currentPlaylist.saveTracks([]);
}

function search() {
  loading.value = true;
  currentTrackIndex.value = 0;
  api.track.search().then((success) => {
    tracks.value = success.data.tracks;
    currentPlaylist.saveTracks(success.data.tracks);
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
  });
}


watch(currentTrackIndex, (newValue) => {
  currentPlaylist.saveCurrentTrackIndex(newValue);
});

const currentPlaylistTrackIndex = computed(() => {
  return(currentPlaylist.getCurrentIndex);
});

watch(currentPlaylistTrackIndex, (newValue) => {
  currentTrackIndex.value = newValue;
});

function onPreviusPlaylist() {
  if (currentTrackIndex.value > 0) {
    currentTrackIndex.value--;
  }
}

function onNextPlaylist() {
  if (currentTrackIndex.value < tracks.value.length - 1) {
    currentTrackIndex.value++;
  }
}

tracks.value = currentPlaylist.getTracks;
currentTrackIndex.value = currentPlaylist.getCurrentIndex;

</script>
