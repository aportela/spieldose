<template>
  <li class="is-size-6-5">
    <q-icon name="link" size="sm" />
    <span v-if="artist.name"><router-link :to="{ name: 'artist', params: { name: artist.name } }">{{
      artist.name }}</router-link></span>
    <span class="q-ml-sm" v-if="artist.playCount"> ({{ artist.playCount }} plays)</span>
    <span class="q-ml-sm" v-if="artist.addedTimestamp"> {{ diff }} hours ago</span>
  </li>
</template>

<script setup>
import { date } from 'quasar';

const diff = date.getDateDiff(Date.now(), props.artist.addedTimestamp * 1000, 'hours');

const props = defineProps({
  artist: {
    type: Object,
    required: true
  }
})

</script>
