<template>
  <ol class="pl-5 is-size-6-5" v-if="itemsType == 'tracks'">
    <li class="is-size-6-5" v-for="item in items" :key="item.id">
      <q-icon name="play_arrow" size="sm" title="play track" class="cursor-pointer" @click="onPlayTracks([item])" />
      <span>{{ item.title }}</span>
      <span v-if="item.artist"> / <router-link :to="{ name: 'artist', params: { name: item.artist } }">{{
        item.artist }}</router-link></span>
      <span v-if="true || showPlayCount"> ({{ item.playCount }} plays)</span>
    </li>
  </ol>
  <ol class="pl-5 is-size-6-5" v-else-if="itemsType == 'albums'">
    <li class="is-size-6-5" v-for="item in items" :key="item.id">
      <q-icon name="play_arrow" size="sm" title="play track" class="cursor-pointer" @click="onPlayTracks([item])" />
      <span>{{ item.title }}</span>
      <span v-if="item.artist"> / <router-link :to="{ name: 'artist', params: { name: item.artist } }">{{
        item.artist }}</router-link></span>
      <span v-if="true || showPlayCount"> ({{ item.playCount }} plays)</span>
    </li>
  </ol>
  <ol class="pl-5 is-size-6-5" v-else-if="itemsType == 'artists'">
    <li class="is-size-6-5" v-for="item in items" :key="item.id">
      <q-icon name="play_arrow" size="sm" title="play track" class="cursor-pointer" @click="onPlayTracks([item])" />
      <span>{{ item.title }}</span>
      <span v-if="item.artist"> / <router-link :to="{ name: 'artist', params: { name: item.artist } }">{{
        item.artist }}</router-link></span>
      <span v-if="true || showPlayCount"> ({{ item.playCount }} plays)</span>
    </li>
  </ol>
  <ol class="pl-5 is-size-6-5" v-else-if="itemsType == 'genres'">
    <li class="is-size-6-5" v-for="item in items" :key="item.id">
      <q-icon name="play_arrow" size="sm" title="play track" class="cursor-pointer" @click="onPlayTracks([item])" />
      <span>{{ item.title }}</span>
      <span v-if="item.artist"> / <router-link :to="{ name: 'artist', params: { name: item.artist } }">{{
        item.artist }}</router-link></span>
      <span v-if="true || showPlayCount"> ({{ item.playCount }} plays)</span>
    </li>
  </ol>
</template>

<script setup>
import { ref } from 'vue'
import { api } from 'boot/axios'
import { usePlayer } from 'stores/player';
import { useCurrentPlaylistStore } from 'stores/currentPlaylist'

const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();

const props = defineProps({
  itemsType: {
    type: String,
    required: true
  },
  items: {
    type: Array,
    required: true
  }
})


function onPlayTracks(tracks) {
  player.stop();
  currentPlaylist.saveTracks(tracks);
  player.interact();
  player.play(false);
}

</script>
