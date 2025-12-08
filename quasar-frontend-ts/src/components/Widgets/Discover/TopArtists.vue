<template>
  <q-card class="q-ma-sm">
    <q-card-section class="text-center">
      Top artists
      <q-tabs v-model="tabModel" dense no-caps class="text-grey" active-color="primary" indicator-color="primary"
        align="justify" narrow-indicator>
        <q-tab v-for="tab in tabs" :name="tab.name" :label="tab.label" :key="tab.name" @click="onRefresh" />
      </q-tabs>
    </q-card-section>
    <q-separator />
    <q-card-section>
      <div class="q-gutter-xs row items-start justify-center">
        <ArtistAvatarLink v-for="artist in artists" :key="artist._id" :name="artist.name" :mb-id="artist.mbId"
          :image="artist.image" :total-tracks="artist.totalTracks" />
      </div>
    </q-card-section>
  </q-card>
</template>
<script setup lang="ts">

import { ref, onMounted, shallowRef } from 'vue';
import { api } from 'src/composables/api';
import { uid } from 'quasar';
import { default as ArtistAvatarLink } from 'src/components/ArtistAvatarLink.vue';

const tabs = [
  { label: "Today", name: "tabToday" },
  { label: "This week", name: "tabThisWeek" },
  { label: "This month", name: "tabThisMonth" },
  { label: "This year", name: "tabThisYear" },
  { label: "Global", name: "tabGlobal" },
];

const tabModel = ref<string>(tabs[tabs.length - 1]!.name);

const artists = shallowRef([]);

const onRefresh = () => {
  api.discover.artist({}, 1, 6, "", "", true).then((successResponse) => {
    artists.value = successResponse.data.artists.map((artist) => { artist._id = uid(); return (artist); });
  }).catch((errorResponse) => {
    console.error(errorResponse);
  }).finally(() => { });
};

onMounted(() => {
  onRefresh();
});
</script>