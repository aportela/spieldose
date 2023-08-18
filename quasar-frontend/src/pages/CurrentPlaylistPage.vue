<template>
  <leftSidebar></leftSidebar>
  <div class="q-pa-md">


    <q-btn-group spread class="q-mb-md">
      <q-btn outline color="dark" label="Clear" icon="clear" @click="clear" />
      <q-btn outline color="dark" label="Random" icon="shuffle" @click="search" />
      <q-btn outline color="dark" label="Previous" icon="skip_previous" />
      <q-btn outline color="dark" label="Play" icon="play_arrow" />
      <q-btn outline color="dark" label="Next" icon="skip_next" />
      <q-btn outline color="dark" label="Download" icon="save_alt" />
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
        <tr v-for="track, index in tracks" :key="track.id" class="non-selectable cursor-pointer" :class="{'bg-pink text-white': currentTrackIndex == index }" @click="currentTrackIndex = index">
          <td class="text-right"><q-icon name="play_arrow" size="sm" class="q-mr-sm" v-if="currentTrackIndex == index"></q-icon>{{ index + 1 }}/32</td>
          <td class="text-left">{{ track.title }}</td>
          <td class="text-left"><router-link :class="{ 'text-white text-bold': currentTrackIndex == index }" :to="{ name: 'artist', params: { name: track.artist }}"><q-icon name="link" class="q-mr-sm"></q-icon>{{ track.artist }}</router-link></td>
          <td class="text-left"><router-link :class="{ 'text-white text-bold': currentTrackIndex == index }" :to="{ name: 'artist', params: { name: track.albumArtist }}"><q-icon name="link" class="q-mr-sm"></q-icon>{{ track.albumArtist }}</router-link></td>
          <td class="text-left">{{ track.album }}<span class="is-clickable"><i class="fas fa-link ml-1"></i></span></td>
          <td class="text-right">{{  track.trackNumber }}</td>
          <td class="text-right">{{ track.year }}</td>
        </tr>
      </tbody>
    </q-markup-table>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { default as leftSidebar } from 'components/AppLeftSidebar.vue';
import { api } from 'boot/axios'

const tracks = ref([]);

const currentTrackIndex = ref(0);

function clear() {
  tracks.value = [];
}

function search() {
  currentTrackIndex.value = 0;
  api.track.search().then((success) => {
    tracks.value = success.data.tracks;
  }).catch((error) => {
  });
}
search();

</script>
