<template>
  <li class="is-size-6-5">
    <q-icon name="play_arrow" size="sm" title="play track" class="cursor-pointer" @click="playTrack(track)" />
    <span>{{ track.title }}</span>
    <span v-if="track.artist"> / <router-link :to="{ name: 'artist', params: { name: track.artist } }">{{
      track.artist }}</router-link></span>
    <span class="q-ml-sm" v-if="track.playCount"> ({{ track.playCount }} plays)</span>
    <span class="q-ml-sm" v-if="track.addedTimestamp"> {{ diff }} hours ago</span>
  </li>
</template>

<script setup>
import { date } from 'quasar';
import { usePlayer } from 'stores/player';
import { useCurrentPlaylistStore } from 'stores/currentPlaylist'

const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();

const props = defineProps({
  track: {
    type: Object,
    required: true
  }
})

const diff = date.getDateDiff(Date.now(), props.track.addedTimestamp * 1000, 'hours');

function playTrack(track) {
  player.stop();
  currentPlaylist.saveTracks([track]);
  player.interact();
  player.play(false);
}

</script>
