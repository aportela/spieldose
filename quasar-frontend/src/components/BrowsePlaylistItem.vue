<template>
  <q-card class="shadow-box shadow-10" bordered>
    <q-card-section>{{ playlist.id != "00000000-0000-0000-0000-000000000000" ? playlist.name : t("My favourite tracks")
    }}</q-card-section>
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
    <q-card-section style="width: 350px; height: 140px;" v-else>
      <div class="row">
        <div class="col-12" style="height: 100px;">
          <q-avatar v-for="n, index in [5, 4, 3, 2, 1, 0]" :key="n" size="100px" class="overlapping" :style="`left: ${(index) * 49}px`">
            <img :src="playlist.covers[n]" :class="'mosaic_cover_element rotate-' + (45 * n)" v-if="playlist.covers[n]" />
            <div v-else class="no_cover" :style="'background: ' + getRandomColor()"></div>
          </q-avatar>
        </div>
      </div>
    </q-card-section>
    <q-separator />
    <q-card-section>
      <q-btn-group spread outline>
        <q-btn :label="t('Play')" stack icon="play_arrow" @click.prevent="onPlay" />
        <q-btn :label="t('Remove')" stack icon="delete" @click.prevent="onDelete" :disable="playlist.allowDelete" />
      </q-btn-group>
    </q-card-section>
    <q-separator />
    <q-card-section class="text-right">
      <span>{{ playlist.trackCount }} {{ t(playlist.trackCount > 1 ? "tracks" : "track") }}</span>
      {{ t('by') }} <router-link :to="{ name: 'playlists', query: routeQueryParams }"> {{ playlist.owner.name }}</router-link>
      <LabelTimestampAgo className="q-ml-xs" :timestamp="playlist.updated * 1000"></LabelTimestampAgo>
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

import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useI18n } from "vue-i18n";

import { default as LabelTimestampAgo } from "components/LabelTimestampAgo.vue";

const route = useRoute();

const { t } = useI18n();

/**
  * Vinyl disc icon credits: Jordan Green (http://www.jordangreenphoto.com/)
  * https://jordygreen.deviantart.com/art/Vinyl-Disc-Icon-Updated-57968239
*/

const defaultImage = 'images/vinyl-small.png';

const props = defineProps(['playlist', 'mode']);
const emit = defineEmits(['play', 'delete']);

const showMosaic = computed(() => { return (props.mode == 'mosaic') });

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

const routeQueryParams = ref(Object.assign({}, route.query || {}));
routeQueryParams.value.userId = props.playlist.owner.id;
routeQueryParams.value.type = "userPlaylists";

</script>
