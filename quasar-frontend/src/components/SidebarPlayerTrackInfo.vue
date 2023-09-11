<template>
  <div id="song_info" v-if="currentElement && currentElement.track">
    <p class="song_info_track text-center text-weight-bolder" style="color: #d30320;">{{ currentElement.track.title || "&nbsp;" }}</p>
    <p class="song_info_album text-center">{{ currentElement.track.album.title || "&nbsp;" }}</p>
    <p class="song_info_artist text-center">
      <router-link v-if="currentElement.track.artist.name" style="text-decoration: none;"
        :to="{ name: 'artist', params: { name: currentElement.track.artist.name } }">{{ currentElement.track.artist.name }}</router-link>
      <span v-else>&nbsp;</span>
    </p>
  </div>
  <div id="song_info" v-else-if="currentElement && currentElement.radioStation">
    <p class="song_info_track text-center text-weight-bolder" style="color: #d30320;">{{ currentElement.radioStation.name || "&nbsp;" }}</p>
    <p class="song_info_album text-center">
      <q-btn-group outline>
      <q-btn outline size="sm" icon="playlist_play" label="playlist" :disable="! currentElement.radioStation.playlist" :href="currentElement.radioStation.playlist" target="_new" />
      <q-btn outline size="sm" icon="play_arrow" label="direct stream" :disable="! currentElement.radioStation.directStream" :href="currentElement.radioStation.directStream" target="_new"/>
    </q-btn-group>
    </p>
    <p class="song_info_artist text-center">
      <a v-if="currentElement.radioStation.url" :href="currentElement.radioStation.url" target="_blank" style="text-decoration: none;">{{ currentElement.radioStation.url }}</a>
      <span>&nbsp;</span>
    </p>
  </div>
</template>

<script setup>

const props = defineProps({
  currentElement: Object
});

</script>
