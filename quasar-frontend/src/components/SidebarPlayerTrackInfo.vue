<template>
  <div id="song_info">
    <p class="song_info_track text-center text-weight-bolder" style="color: #d30320;">{{ currentTrack ? currentTrack.title: "No title" }}</p>
    <p class="song_info_album text-center">{{ currentTrack ? currentTrack.album: "No album" }}</p>
    <p class="song_info_artist text-center"><a href="#/app/artist/The%20Who" style="text-decoration: none;">{{ currentTrack ? currentTrack.artist: "No artist" }}</a>
    </p>
  </div>
</template>

<script setup>

const props = defineProps({
  currentTrack: Object
});

</script>
