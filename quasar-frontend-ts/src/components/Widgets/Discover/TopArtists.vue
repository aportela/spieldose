<template>
  <q-card class="q-ma-sm">
    <q-card-section class="text-center">
      Top artists
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
          v-for="artist in artists" :key="artist._id" :title="artist.name">
          <q-img v-if="artist.image" :src="getSmallURL(artist.image)" fit="cover" :ratio="1"
            style="min-width: 10em; width: 20%; " />
          <q-skeleton v-else width="100%" style="height: auto; aspect-ratio: 1; margin: 0px auto" animation="none" />
          <span class="artist_name">{{ artist.name }}</span>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>
<script setup lang="ts">

import { ref, onMounted, shallowRef, reactive } from 'vue';
import { api } from 'src/composables/api';
import { uid } from 'quasar';
import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajax-state";
//import { default as ArtistAvatarLink } from 'src/components/ArtistAvatarLink.vue';
import {
  type BrowseArtistsResponse as BrowseArtistsResponseInterface,
  type BrowseArtistItemResponse as BrowseArtistItemResponseInterface,
} from "src/types/api-responses";
import { getSmallURL } from "src/composables/thumbnail";

const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

const tabs = [
  { label: "Today", name: "tabToday" },
  { label: "This week", name: "tabThisWeek" },
  { label: "This month", name: "tabThisMonth" },
  { label: "This year", name: "tabThisYear" },
  { label: "Global", name: "tabGlobal" },
];

class Artist implements BrowseArtistItemResponseInterface {
  _id: string;
  name: string;
  mbId: string | null;
  image: string | null;
  totalTracks: number;

  constructor(item: BrowseArtistItemResponseInterface) {
    this._id = uid();
    this.name = item.name;
    this.mbId = item.mbId;
    this.image = item.image;
    this.totalTracks = item.totalTracks;
  }
}

const tabModel = ref<string>(tabs[tabs.length - 1]!.name);

const artists = shallowRef<Artist[]>([]);

const count = 8;
const onRefresh = () => {
  if (!state.ajaxRunning) {
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.discover.artist({}, 1, count, "", "", true).then((successResponse: BrowseArtistsResponseInterface) => {
      artists.value = successResponse.data.artists.map((item) => {
        return (new Artist(item));
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
