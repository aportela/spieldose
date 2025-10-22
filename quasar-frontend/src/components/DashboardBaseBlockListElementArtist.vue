<template>
  <li class="is-size-6-5 dashboard_list_item" v-if="artist">
    <slot name="prepend">
    </slot>
    <q-icon name="link" size="sm" class="q-mr-xs" />
    <span v-if="artist.name"><router-link
        :to="{ name: 'artist', params: { name: artist.name }, query: { mbid: artist.mbId, tab: 'overview' } }">{{
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

<style lang="css">
li.dashboard_list_item {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>