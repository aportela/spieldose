<template>
  <li class="is-size-6-5">
    <q-icon name="play_arrow" size="sm" title="play track" class="cursor-pointer" @click="playAlbum(album)" />
    <span>{{ album.title }}</span>
    <span v-if="album.albumArtistName"> / <router-link
        :to="{ name: 'artist', params: { name: album.albumArtistName } }">{{
          album.albumArtistName }}</router-link></span>
    <span v-if="album.year"> [{{ album.year }}] </span>
    <span> ({{ album.playCount }} plays)</span>
  </li>
</template>

<script setup>
const props = defineProps({
  album: {
    type: Object,
    required: true
  }
})

</script>
