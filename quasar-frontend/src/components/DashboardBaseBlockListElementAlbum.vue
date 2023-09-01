<template>
  <li class="is-size-6-5 dashboard_list_item" v-if="album">
    <slot name="prepend">
    </slot>
    <q-icon name="album" size="sm" class="q-mr-xs" />
    <span>{{ album.title }}</span>
    <span v-if="album.year"> ({{ album.year }}) </span>
    <span v-if="album.albumArtistName"> / <router-link
        :to="{ name: 'artist', params: { name: album.albumArtistName } }">{{
          album.albumArtistName }}</router-link></span>

    <slot name="append">
    </slot>
  </li>
</template>

<style>
li.dashboard_list_item {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>

<script setup>

const props = defineProps({
  album: {
    type: Object
  }
})

</script>
