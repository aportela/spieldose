<template>
  <li class="is-size-6-5">
    <q-icon name="link" size="sm" />
    <span v-if="artist.name"><router-link :to="{ name: 'artist', params: { name: artist.name } }">{{
      artist.name }}</router-link></span>
    <span> ({{ artist.playCount }} plays)</span>
  </li>
</template>

<script setup>
const props = defineProps({
  artist: {
    type: Object,
    required: true
  }
})

</script>
