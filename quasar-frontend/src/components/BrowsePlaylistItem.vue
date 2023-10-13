<template>
  <q-card class="shadow-box shadow-10" bordered>
    <q-card-section>{{ playlist.name }}</q-card-section>
    <q-separator />
    <q-card-section v-if="showMosaic" style="width: 350px;">
      <div class="row">
        <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[0] || defaultImage" /></div>
        <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[1] || defaultImage" /></div>
        <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[2] || defaultImage" /></div>
      </div>
      <div class="row">
        <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[3] || defaultImage" /></div>
        <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[4] || defaultImage" /></div>
        <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[5] || defaultImage" /></div>
      </div>
      <div class="row">
        <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[6] || defaultImage" /></div>
        <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[7] || defaultImage" /></div>
        <div class="col-4"><img class="mosaic_cover_element" :src="playlist.covers[8] || defaultImage" /></div>
      </div>
    </q-card-section>
    <q-card-section style="width: 350px; height: 140px;" v-else-if="showVinylCollection">
      <q-avatar v-for="n in 9" :key="n" size="100px" class="overlapping" :style="`left: ${n * 25}px`">
        <img :src="playlist.covers[n]" :class="'mosaic_cover_element rotate-' + (45 * (n -1))"
          v-if="playlist.covers[n]" />
        <div v-else class="no_cover" :style="'background: ' + getRandomColor()"></div>
      </q-avatar>
    </q-card-section>
    <q-separator />
    <q-card-section>
      <q-btn-group spread outline>
        <q-btn label="play" stack icon="play_arrow" @click.prevent="onPlay" />
        <q-btn label="edit" stack icon="edit" disabled/>
        <q-btn label="delete" stack icon="delete" @click.prevent="onDelete" :disable="playlist.id == '00000000-0000-0000-0000-000000000000'"/>
      </q-btn-group>
    </q-card-section>
    <q-separator />
    <q-card-section class="text-right">
      <span class="q-mr-sm">{{ playlist.trackCount }} track/s</span>
      <router-link :to="{ name: 'playlistsByUserId', params: { id: playlist.owner.id }}">by {{  playlist.owner.name }}</router-link>
      <LabelTimestampAgo className="q-ml-sm" :timestamp="playlist.updated * 1000"></LabelTimestampAgo>
    </q-card-section>
  </q-card>
</template>

<style>
div.no_cover {
  width: 100px;
  height: 100px;
}

img.mosaic_cover_element {
  width: 100px;
  max-width: 100%;
  height: auto;
  aspect-ratio: 1 / 1;
}

.overlapping {
  border: 2px solid white;
  position: absolute;
}
</style>

<script setup>

import { computed } from "vue";
import { useQuasar } from "quasar";

import { default as LabelTimestampAgo } from "components/LabelTimestampAgo.vue";
/**
  * Vinyl disc icon credits: Jordan Green (http://www.jordangreenphoto.com/)
  * https://jordygreen.deviantart.com/art/Vinyl-Disc-Icon-Updated-57968239
*/

const $q = useQuasar();

const defaultImage = 'images/vinyl-small.png';

const emit = defineEmits(['play', 'delete']);

const props = defineProps({
  playlist: Object,
  mode: String
});

const showMosaic = computed(() => { return (props.mode == 'mosaic') });

const showVinylCollection = computed(() => { return (props.mode == 'vinyls') });

function onPlay() {
  emit('play', props.playlist.id);
}

function onDelete() {
  emit('delete', props.playlist.id);
}

// https://stackoverflow.com/a/1484514
function getRandomColor() {
  const allowed = "ABCDEF0123456789";
  let S = "#";
  while (S.length < 7) {
    S += allowed.charAt(Math.floor((Math.random() * 16) + 1));
  }
  return (S);
}

</script>
