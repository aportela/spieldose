<template>
  <q-card class="q-ma-sm">
    <q-card-section class="text-center">
      Top albums
      <q-tabs v-model="tabModel" dense no-caps class="text-grey" active-color="primary" indicator-color="primary"
        align="justify" narrow-indicator>
        <q-tab v-for="tab in tabs" :name="tab.name" :label="tab.label" :key="tab.name" @click="onRefresh"
          :disable="state.ajaxRunning" />
      </q-tabs>
    </q-card-section>
    <q-separator />
    <q-card-section>
      <!--
      <div class="q-gutter-xs row items-start justify-center">
        <ArtistAvatarLink v-for="artist in artists" :key="artist._id" :name="artist.name" :mb-id="artist.mbId"
          :image="artist.image" :total-tracks="artist.totalTracks" />
      </div>
      -->
      <div class="q-gutter-md row items-start justify-center">
        <div class="text-center cursor-pointer" style="min-width: 10em; width: 20%; overflow: hidden;"
          v-for="album in albums" :key="album._id" :title="album.title">
          <q-img :src="album.image || '#'" fit="cover" :ratio="1" style="min-width: 10em; width: 20%;"
            @error="album.image = 'vectors/Vinyl_record.svg'" />
          <span class="artist_name">{{ album.title }}</span>
          <p>({{ album.year || "unknown" }})</p>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>
<script setup lang="ts">

import { ref, onMounted, shallowRef, reactive } from 'vue';
import { api } from 'src/composables/api';
import { uid } from 'quasar';
import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajaxAtate";
//import { default as ArtistAvatarLink } from 'src/components/ArtistAvatarLink.vue';
import {
  type BrowseAlbumsResponse as BrowseAlbumsResponseInterface,
  type BrowseAlbumItemResponse as BrowseAlbumItemResponseInterface,
} from "src/types/apiResponses";
import { getSmallURL } from "src/composables/thumbnail";

const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

const tabs = [
  { label: "Today", name: "tabToday" },
  { label: "This week", name: "tabThisWeek" },
  { label: "This month", name: "tabThisMonth" },
  { label: "This year", name: "tabThisYear" },
  { label: "Global", name: "tabGlobal" },
];

class Album implements BrowseAlbumItemResponseInterface {
  _id: string;
  title: string;
  mbId: string | null;
  year: number | null;
  image: string | null;
  artist: {
    name: string | null;
    mbId: string | null;
  }

  constructor(item: BrowseAlbumItemResponseInterface) {
    this._id = uid();
    this.title = item.title;
    this.mbId = item.mbId;
    this.year = item.year;
    this.image = item.mbId ? getSmallURL(`https://coverartarchive.org/release/${item.mbId}/front-250`) : "vectors/Vinyl_record.svg";
    this.artist = {
      name: null,
      mbId: null,
    };
  }
}
const tabModel = ref<string>(tabs[tabs.length - 1]!.name);

const albums = shallowRef<Album[]>([]);

const count = 8;

const onRefresh = () => {
  if (!state.ajaxRunning) {
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.discover.album({}, 1, count, "", "", true).then((successResponse: BrowseAlbumsResponseInterface) => {
      albums.value = successResponse.data.albums.map((item) => {
        return (new Album(item));
      });
    }).catch((errorResponse) => {
      console.error(errorResponse);
    }).finally(() => {
      state.ajaxRunning = false;
    });
  }
};

onMounted(() => {
  onRefresh();
});
</script>

<style lang="css" scoped>
.artist_name {
  display: inline-block;
  width: 100%;
  text-overflow: ellipsis;
  text-wrap: nowrap;
  overflow: hidden;
}
</style>