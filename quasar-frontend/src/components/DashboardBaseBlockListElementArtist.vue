<template>
  <li class="is-size-6-5" v-if="artist">
    <slot name="prepend">
    </slot>
    <q-icon name="link" size="sm" class="q-mr-xs" />
    <span v-if="artist.name"><router-link :to="{ name: 'artist', params: { name: artist.name } }">{{
      artist.name }}</router-link></span>
    <slot name="append">
    </slot>
  </li>
</template>

<script setup>

const props = defineProps({
  artist: {
    type: Object
  }
})

</script>
