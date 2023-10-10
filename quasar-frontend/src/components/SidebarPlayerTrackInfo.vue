<template>
  <div class="q-px-md" style="max-width: 400px;" v-if="isTrack">
    <p class="text-center text-weight-bolder ellipsis text-pink" :title="track.title || '&nbsp;'">{{
      track.title || null }}</p>
    <p class="text-center ellipsis" :title="track.album.title || null">{{
      track.album.title || "&nbsp;" }}</p>
    <p class="text-center ellipsis">
      <router-link v-if="track.artist.name" style="text-decoration: none;"
        :to="{ name: 'artist', params: { name: track.artist.name }, query: { mbid: track.artist.mbId, tab: 'overview' } }"
        :title="track.artist.name">{{ track.artist.name }}</router-link>
      <span v-else>&nbsp;</span>
    </p>
  </div>
  <div class="q-px-md" style="max-width: 400px;" v-else-if="isRadioStation">
    <p class="text-center text-weight-bolder ellipsis text-pink" :title="radioStation.name">
      {{ radioStation.name || "&nbsp;" }}</p>
    <p class="text-center">
      <q-btn-group outline>
        <q-btn outline size="sm" icon="playlist_play" label="playlist" :disable="!radioStation.playlist"
          :href="radioStation.playlist" target="_new" />
        <q-btn outline size="sm" icon="play_arrow" label="direct stream"
          :disable="!radioStation.directStream" :href="radioStation.directStream"
          target="_new" />
      </q-btn-group>
    </p>
    <p class="text-center ellipsis">
      <a v-if="radioStation.url" :href="radioStation.url" target="_blank"
        style="text-decoration: none;">{{ radioStation.url }}</a>
      <span>&nbsp;</span>
    </p>
  </div>
  <div v-else>
    <p class="text-weight-bolder">&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
  </div>
</template>

<script setup>

import { computed } from "vue";

const props = defineProps({
  track: Object,
  radioStation: Object
});

const isTrack = computed(() => props.track != null);
const isRadioStation = computed(() => props.radioStation != null);

</script>
