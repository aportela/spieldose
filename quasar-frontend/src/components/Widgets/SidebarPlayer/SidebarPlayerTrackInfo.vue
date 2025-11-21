<template>
  <div class="q-px-md" style="max-width: 400px;" v-if="currentPlaylistItemStore.isTrack">
    <p class="text-center text-weight-bolder ellipsis text-pink q-my-none"
      :title="currentPlaylistItemStore.trackTitle || '&nbsp;'">{{
        currentPlaylistItemStore.trackTitle || '&nbsp;' }}</p>
    <p class="text-center ellipsis">
      <router-link v-if="currentPlaylistItemStore.trackArtistName" style="text-decoration: none;"
        :to="{ name: 'artist', params: { name: currentPlaylistItemStore.trackArtistName }, query: { mbid: currentPlaylistItemStore.trackArtistMBId, tab: 'overview' } }"
        :title="currentPlaylistItemStore.trackArtistName">by {{ currentPlaylistItemStore.trackArtistName
        }}</router-link>
      <span v-else>&nbsp;</span>
    </p>

    <p class="text-center ellipsis" :title="currentPlaylistItemStore.trackAlbumTitle">
      <router-link style="text-decoration: none;"
        :to="{ name: 'album', params: { title: currentPlaylistItemStore.trackAlbumTitle }, query: { mbId: currentPlaylistItemStore.trackAlbumMBId, artistName: currentPlaylistItemStore.trackAlbumArtistName, artistMBId: currentPlaylistItemStore.trackAlbumArtistMBId, year: currentPlaylistItemStore.trackAlbumYear } }"
        v-if="currentPlaylistItemStore.trackAlbumTitle">{{ currentPlaylistItemStore.trackAlbumTitle }}
        <br /><span v-if="currentPlaylistItemStore.trackAlbumArtistName">by {{
          currentPlaylistItemStore.trackAlbumArtistName }}</span>
        <br /><span v-if="currentPlaylistItemStore.trackAlbumYear">({{ currentPlaylistItemStore.trackAlbumYear
        }})</span>
      </router-link>
      <span v-else>&nbsp;</span>
    </p>
  </div>
  <div class="q-px-md" style="max-width: 400px;" v-else-if="currentPlaylistItemStore.isRadioStation">
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

<script setup>

import { useCurrentPlaylistItemStore } from 'src/stores/currentPlaylistItem';

const currentPlaylistItemStore = useCurrentPlaylistItemStore();

</script>