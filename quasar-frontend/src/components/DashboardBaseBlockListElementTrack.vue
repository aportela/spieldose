<template>
  <li class="is-size-6-5 dashboard_list_item" v-if="track">
    <slot name="prepend">
    </slot>
    <q-icon name="play_arrow" size="sm" title="play track" class="cursor-pointer q-mr-xs" @click="playTrack(track)" />
    <span>{{ track.title }}</span>
    <span v-if="track.artist.name"> / <router-link :to="{ name: 'artist', params: { name: track.artist.name } }">{{
      track.artist.name }}</router-link></span>
    <slot name="append">
    </slot>
  </li>
</template>

<style>
li.dashboard_list_item {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>

<script setup>

import { usePlayer } from 'stores/player';
import { useCurrentPlaylistStore } from 'stores/currentPlaylist'

const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();

const props = defineProps({
  track: {
    type: Object
  }
})

function playTrack(track) {
  player.stop();
  currentPlaylist.saveElements([track]);
  player.interact();
  player.play(false);
}

</script>
