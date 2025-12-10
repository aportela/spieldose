<template>
  <div class="q-px-md" style="max-width: 400px;" v-if="true || currentPlayListsStore.currentActivePlayListItem?.isFile">
    <p class="text-center text-weight-bolder ellipsis text-pink q-my-none"
      :title="currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.title ?? '&nbsp;'">{{
        currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.title ?? '&nbsp;' }}</p>
    <p class="text-center ellipsis">
      <router-link v-if="currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.artist.name"
        style="text-decoration: none;"
        :to="{ name: 'artist', params: { name: currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.artist.name }, query: { mbid: currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.artist.mbId, tab: 'overview' } }"
        :title="currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.artist.name">by {{
          currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.artist.name
        }}</router-link>
      <span v-else>&nbsp;</span>
    </p>

    <p class="text-center ellipsis"
      :title="currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.album.title ?? undefined">
      <router-link style="text-decoration: none;" :to="{
        name: 'album',
        params: {
          title: currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.album.title
        },
        query: {
          mbId: currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.album.mbId,
          artistName: currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.album.artist.name,
          artistMBId: currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.album.artist.mbId,
          year: currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.album.year
        }
      }" v-if="currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.album.title">{{
        currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.album.title }}
        <br /><span v-if="currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.album.artist.name">by {{
          currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.album.artist.name }}</span>
        <br /><span v-if="currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.album.year">({{
          currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.album.year }})</span>
      </router-link>
      <span v-else>&nbsp;</span>
    </p>
  </div>
  <div class="q-px-md" style="max-width: 400px;" v-else-if="currentPlayListsStore.currentActivePlayListItem?.isStream">
    <!--
    <p class="text-center text-weight-bolder ellipsis text-pink" :title="radioStation.name">
      {{ radioStation.name || "&nbsp;" }}</p>
    <p class="text-center">
      <q-btn-group outline>
        <q-btn outline size="sm" icon="playlist_play" label="playlist" :disable="!radioStation.playlist"
          :href="radioStation.playlist" target="_new" />
        <q-btn outline size="sm" icon="play_arrow" label="direct stream" :disable="!radioStation.directStream"
          :href="radioStation.directStream" target="_new" />
      </q-btn-group>
    </p>
    <p class="text-center ellipsis">
      <a v-if="radioStation.url" :href="radioStation.url" target="_blank" style="text-decoration: none;">{{
        radioStation.url }}</a>
      <span>&nbsp;</span>
    </p>
    -->
  </div>
  <div v-else>
    TODO
    <p class="text-weight-bolder">&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
  </div>
</template>

<script setup lang="ts">

import { useCurrentPlayListsStore } from "src/stores/currentPlayLists";

const currentPlayListsStore = useCurrentPlayListsStore();

</script>