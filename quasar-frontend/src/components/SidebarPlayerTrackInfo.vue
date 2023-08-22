<template>
  <div id="song_info">
    <p class="song_info_track text-center text-weight-bolder" style="color: #d30320;">{{ currentTrack ? currentTrack.title: "No title" }}</p>
    <p class="song_info_album text-center">{{ currentTrack ? currentTrack.album: "No album" }}</p>
    <p class="song_info_artist text-center">
        <router-link v-if="currentTrack && currentTrack.artist" style="text-decoration: none;" :to="{ name: 'artist', params: { name: currentTrack.artist }}" >{{ currentTrack.artist }}</router-link>
        <span v-else>No artist</span>
      </p>
  </div>
</template>

<script setup>

const props = defineProps({
  currentTrack: Object
});

</script>
