<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <q-breadcrumbs-el icon="analytics" :label="t('Home')" />
    </q-breadcrumbs>
    <div class="row q-col-gutter-lg">
      <div class="col-3" v-for="blockName in blockNames" :key="blockName">
        <q-card class="card flat bordered">
          <q-card-section>
            <div class="text-h6 text-center">{{ blockName }}</div>
          </q-card-section>
          <q-separator dark inset />
          <q-card-section>
            <div class="row overflow-hidden" v-for="row, rowIndex in rows" :key="row">
              <div class="col-xs-6 col-md-4 col-lg-3 col-xl-3" v-for="column in columns" :key="column"
                :style="'background-color: ' + getRandomColor() + ';'">
                <img class="spieldose-album-cover-tile"
                  :src="getImageSourceFromIndex((rows.length * rowIndex) + column)" v-if="images.length > 0"
                  @error="onImageError($event)">
                <img class="spieldose-album-cover-tile" :src="defaultImage" v-else>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-card>
</template>

<script setup>
import { ref } from "vue";

const { api } = useAPI();
import { useI18n } from "vue-i18n";
import { useAPI } from "src/composables/useAPI";


const { t } = useI18n();

const rows = [0, 1, 2];
const columns = [0, 1, 2, 3];

const blockNames = ["New artists", "Random artists", "Featured artists", "Recommended artists for you"]
const images = ref([]);

const defaultImage = 'images/vinyl-medium.png';

// https://stackoverflow.com/a/1484514
function getRandomColor() {
  const allowed = "ABCDEF0123456789";
  let S = "#";
  while (S.length < 7) {
    S += allowed.charAt(Math.floor((Math.random() * 16) + 1));
  }
  return (S);
}

function getImageSourceFromIndex(index) {
  if (index < images.value.length) {
    return (images.value[index]);
  } else {
    return (defaultImage);
  }
}

function loadRandomAlbumImages() {
  api.album.getSmallRandomCovers(144).then(response => {
    images.value = Array.isArray(response.data.coverURLs) ? response.data.coverURLs : [];
  }).catch(error => {
    console.error(error.response);
  });
}

function onImageError(event) {
  event.target.src = defaultImage;
}


loadRandomAlbumImages();
</script>

<style lang="css" scoped>
img.spieldose-album-cover-tile {
  width: 100%;
  max-width: 100%;
  height: 100%;
  aspect-ratio: 1 / 1;
}
</style>