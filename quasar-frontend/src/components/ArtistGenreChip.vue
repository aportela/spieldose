<template>
  <router-link :to="{ name: 'artists', query: { page: 1, genre: name }}">
    <q-chip size="md" icon="tag" truncate-chip-labels>{{ name }}</q-chip>
  </router-link>
</template>

<script setup>

const props = defineProps({
  name: String
});

</script>
