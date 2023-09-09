<template>
  <q-card class="col-xl-2 col-lg-3 col-md-12 col-sm-12 col-xs-12 shadow-box shadow-10 q-mt-lg" bordered>
    <q-card-section>
      {{ playlist.name }} ({{ playlist.trackCount }} track/s)
    </q-card-section>
    <q-separator />
    <q-card-section v-if="showMosaic">
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
    <q-card-section style="height: 120px;" v-else-if="showVinylCollection">
      <q-avatar v-for="n in 5" :key="n" size="80px" class="overlapping" :style="`left: ${n * 25}px`">
        <img :src="playlist.covers[n]" :class="'mosaic_cover_element rotate-' + (45 * (n + 3))"
          v-if="playlist.covers[n]" />
        <div v-else class="no_cover" :style="'background: ' + getRandomColor()"></div>
      </q-avatar>
    </q-card-section>
    <q-separator />
    <q-card-section>
      <q-btn-group spread outline>
        <q-btn label="play" stack icon="play_arrow" />
        <q-btn label="edit" stack icon="edit" />
        <q-btn label="delete" stack icon="delete" />
      </q-btn-group>
    </q-card-section>

  </q-card>
</template>

<style>
div.no_cover {
  width: 100px;
  height: 100px;
}

img.mosaic_cover_element {
  width: 100%;
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

import { ref, computed } from "vue";

/**
  * Vinyl disc icon credits: Jordan Green (http://www.jordangreenphoto.com/)
  * https://jordygreen.deviantart.com/art/Vinyl-Disc-Icon-Updated-57968239
*/

const defaultImage = 'images/vinyl.png';

const emit = defineEmits(['play']);

const props = defineProps({
  playlist: Object,
  mode: String
});

const showMosaic = computed(() => { return (props.mode == 'mosaic') });

const showVinylCollection = computed(() => { return (props.mode == 'vinylCollection') });

function onPlay() {
  emit('play');
}

</script>
