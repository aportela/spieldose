<template>
  <li class="is-size-6-5">
    <q-icon name="link" size="sm" />
    <span>{{ genre.name }}</span>
    <span class="q-ml-sm" v-if="genre.playCount"> ({{ genre.playCount }} plays)</span>
    <span class="q-ml-sm" v-if="genre.addedTimestamp"> {{ diff }} hours ago</span>
  </li>
</template>

<script setup>
import { date } from 'quasar';

const diff = date.getDateDiff(Date.now(), props.genre.addedTimestamp * 1000, 'hours');

const props = defineProps({
  genre: {
    type: Object,
    required: true
  }
})

</script>
