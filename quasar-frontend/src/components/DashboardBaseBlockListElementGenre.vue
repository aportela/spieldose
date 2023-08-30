<template>
  <li class="is-size-6-5">
    <q-icon name="link" size="sm" />
    <span>{{ genre.name }}</span>
    <span> ({{ genre.playCount }} plays)</span>
  </li>
</template>

<script setup>
const props = defineProps({
  genre: {
    type: Object,
    required: true
  }
})

</script>
