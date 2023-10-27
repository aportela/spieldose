<template>
  <li class="is-size-6-5 dashboard_list_item" v-if="album">
    <slot name="prepend">
    </slot>
    <q-icon name="album" size="sm" class="q-mr-xs" />
    <router-link style="text-decoration: none;" :to="{ name: 'album', params: { title: album.title }, query: { mbId: album.mbId, artistName: album.albumArtistName, artistMBId: album.albumArtistMBId, year: album.year }}" v-if="album.title">{{ album.title }}</router-link>
    <span v-else>&nbsp;</span>
    <span v-if="album.year"> ({{ album.year }}) </span>
    <span v-if="album.albumArtistName"> / <router-link
        :to="{ name: 'artist', params: { name: album.albumArtistName }, query: { mbid: album.albumArtistMBId, tab: 'overview' } }">{{
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
