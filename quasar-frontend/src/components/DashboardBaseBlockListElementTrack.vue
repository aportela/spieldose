<template>
  <li class="is-size-6-5 dashboard_list_item" v-if="track">
    <slot name="prepend">
    </slot>
    <q-icon name="play_arrow" size="sm" :title="t('play track')" class="cursor-pointer q-mr-xs"
      @click="trackActions.play(track)" />
    <q-icon name="add_box" size="sm" :title="t('enqueue track')" class="cursor-pointer q-mr-xs"
      @click="trackActions.enqueue(track)" />
    <span>{{ track.title }}</span>
    <span v-if="track.artist.name"> / <router-link
        :to="{ name: 'artist', params: { name: track.artist.name }, query: { mbid: track.artist.mbId, tab: 'overview' } }">{{
          track.artist.name }}</router-link></span>
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

import { useI18n } from 'vue-i18n'
import { trackActions } from '../boot/spieldose';

const { t } = useI18n();

const props = defineProps({
  track: {
    type: Object
  }
})

</script>
