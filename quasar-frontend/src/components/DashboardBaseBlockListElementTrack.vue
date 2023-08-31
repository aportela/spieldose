<template>
  <li class="is-size-6-5" v-if="track">
    <slot name="prepend">
    </slot>
    <q-icon name="play_arrow" size="sm" title="play track" class="cursor-pointer" @click="playTrack(track)" />
    <span>{{ track.title }}</span>
    <span v-if="track.artist"> / <router-link :to="{ name: 'artist', params: { name: track.artist } }">{{
      track.artist }}</router-link></span>
    <slot name="append">
    </slot>
  </li>
</template>

<script setup>

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

function playTrack(track) {
  player.stop();
  currentPlaylist.saveTracks([track]);
  player.interact();
  player.play(false);
}

</script>
