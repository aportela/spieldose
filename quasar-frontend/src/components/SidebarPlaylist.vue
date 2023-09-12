<template>
  <div style="" class="bg-white text-dark">
      <q-list>
        <q-item v-for="element, index in elements" :key="element.track.id">
          <q-item-section>
            <q-item-label>{{ element.track.title }}{{ index }}</q-item-label>
            <q-item-label caption lines="2">{{ element.track.artist.name }}<br>{{ element.track.album.title }} ({{  element.track.album.year }}).</q-item-label>
          </q-item-section>
          <q-item-section side top>
            <q-item-label caption>4:34</q-item-label>
            <q-icon name="music_note" color="pink" />
          </q-item-section>
        </q-item>
        <q-separator spaced inset />
        </q-list>
  </div>
</template>

<script setup>

import { ref, watch, computed } from "vue";
import { useCurrentPlaylistStore } from 'stores/currentPlaylist'

const currentPlaylist = useCurrentPlaylistStore();

const elements = ref([]);

elements.value = currentPlaylist.getElements;

</script>
