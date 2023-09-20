<template>
  <div class="q-px-md" style="max-width: 400px;" v-if="isTrack">
    <p class="text-center text-weight-bolder ellipsis text-pink"
      :title="currentElement.track.title || '&nbsp;'">{{ currentElement.track.title || null }}</p>
    <p class="text-center ellipsis" :title="currentElement.track.album.title || null">{{
      currentElement.track.album.title || "&nbsp;" }}</p>
    <p class="text-center ellipsis">
      <router-link v-if="currentElement.track.artist.name" style="text-decoration: none;"
        :to="{ name: 'artist', params: { name: currentElement.track.artist.name } }"
        :title="currentElement.track.artist.name">{{ currentElement.track.artist.name }}</router-link>
      <span v-else>&nbsp;</span>
    </p>
  </div>
  <div class="q-px-md" style="max-width: 400px;" v-else-if="isRadioStation">
    <p class="text-center text-weight-bolder ellipsis text-pink" :title="currentElement.radioStation.name">
      {{ currentElement.radioStation.name || "&nbsp;" }}</p>
    <p class="text-center">
      <q-btn-group outline>
        <q-btn outline size="sm" icon="playlist_play" label="playlist" :disable="!currentElement.radioStation.playlist"
          :href="currentElement.radioStation.playlist" target="_new" />
        <q-btn outline size="sm" icon="play_arrow" label="direct stream"
          :disable="!currentElement.radioStation.directStream" :href="currentElement.radioStation.directStream"
          target="_new" />
      </q-btn-group>
    </p>
    <p class="text-center ellipsis">
      <a v-if="currentElement.radioStation.url" :href="currentElement.radioStation.url" target="_blank"
        style="text-decoration: none;">{{ currentElement.radioStation.url }}</a>
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
  currentElement: Object,
});

const isTrack = computed(() => props.currentElement && props.currentElement.track);
const isRadioStation = computed(() => props.currentElement && props.currentElement.radioStation);

</script>
