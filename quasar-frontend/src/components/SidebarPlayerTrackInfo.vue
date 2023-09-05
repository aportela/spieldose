<template>
  <div id="song_info">
    <p class="song_info_track text-center text-weight-bolder" style="color: #d30320;">{{ currentTrack ?
      currentTrack.title : "&nbsp;" }}</p>
    <p class="song_info_album text-center">{{ currentTrack ? currentTrack.album.title : "&nbsp;" }}</p>
    <p class="song_info_artist text-center">
      <router-link v-if="currentTrack && currentTrack.artist.name" style="text-decoration: none;"
        :to="{ name: 'artist', params: { name: currentTrack.artist.name } }">{{ currentTrack.artist.name }}</router-link>
      <span v-else>&nbsp;</span>
    </p>
  </div>
</template>

<script setup>

const props = defineProps({
  currentTrack: Object
});

</script>
