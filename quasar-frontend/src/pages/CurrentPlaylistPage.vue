<template>
  <leftSidebar></leftSidebar>
  <div class="q-pa-md">


    <q-btn-group spread>
      <q-btn outline color="dark" label="Clear" icon="clear" />
      <q-btn outline color="dark" label="Random" icon="shuffle" @click="search" />
      <q-btn outline color="dark" label="Previous" icon="skip_previous" />
      <q-btn outline color="dark" label="Play" icon="play_arrow" />
      <q-btn outline color="dark" label="Next" icon="skip_next" />
      <q-btn outline color="dark" label="Download" icon="save_alt" />
    </q-btn-group>

    <q-markup-table flat bordered>
      <thead>
        <tr>
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
        <tr v-for="track, index in tracks" :key="track.id">
          <td class="text-right">{{ index + 1 }}/32</td>
          <td class="text-left">{{ track.title }}</td>
          <td class="text-left">{{ track.artist }} <a href="#/app/artist/Texas" class=""><i class="fas fa-link ml-1"></i></a></td>
          <td class="text-left">{{ track.albumArtist }} <span class="is-clickable"><i class="fas fa-link ml-1"></i></span></td>
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
function search() {
  api.track.search().then((success) => {
    tracks.value = success.data.tracks;
  }).catch((error) => {
  });
}
search();

</script>
