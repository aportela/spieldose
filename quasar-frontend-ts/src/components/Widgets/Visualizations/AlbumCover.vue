<template>
  <!--
  vinyl svg credits:
  https://commons.wikimedia.org/wiki/File:Vinyl_record.svg
  -->
  <div v-if="playerStore.currentVinylAnimation === 'vinyl'" @click="toggleAnimation"
    id="spieldose-sidebar-vinyl-container" class="cursor-pointer overflow-hidden relative-position full-width"
    style="background: url(vectors/Vinyl_record.svg) no-repeat; background-size: cover;"
    :class="{ 'spieldose-sidebar-animation-rotation-infinite': playerStore.isPlaying }"
    :title="t('Toggle art animation')">
    <q-img v-if="images.small" :src="images.small" @error="images.small = null" :ratio="1" img-class="vinyl_mini_cover"
      spinner-color="pink"></q-img>
  </div>
  <!--
  cassete vector credits:
  Patrick Schwarz (nablagrange) at https://pixabay.com/vectors/cassette-music-magnetic-tape-7576061/
  -->
  <cassete-tape v-else-if="playerStore.currentVinylAnimation === 'cassete'" @click="toggleAnimation" />
  <div v-else @click="toggleAnimation" class="cursor-pointer" :title="t('Toggle art animation')">
    <q-img v-if="images.normal" :src="images.normal" @error="images.normal = null" alt="Album cover" :ratio="1"
      width="100%" spinner-color="pink" />
    <q-img v-else src="vectors/Vinyl_record.svg" alt="Vinyl" :ratio="1" width="100%" spinner-color="pink" />
  </div>
</template>

<script setup lang="ts">
import { nextTick, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { usePlayerStore } from "src/stores/player";
import { useCurrentPlayListsStore } from "src/stores/currentPlayLists";


import { default as CasseteTape } from "./CasseteTape.vue";

const playerStore = usePlayerStore();

const currentPlayListsStore = useCurrentPlayListsStore();


const { t } = useI18n();

const images = ref({
  normal: currentPlayListsStore.currentActivePlayListItem?.images?.big ?? null,
  small: currentPlayListsStore.currentActivePlayListItem?.images?.medium ?? null,
});
console.log(images);

watch(() => currentPlayListsStore.currentActivePlayListItem?.images?.medium, (newValue) => {
  console.log("cange", newValue);
  images.value.small = null;
  if (newValue) {
    nextTick()
      .then(() => {
        images.value.small = newValue
      }).catch((e) => {
        console.error(e);
      });
  }
});

watch(() => currentPlayListsStore.currentActivePlayListItem?.images?.big, (newValue) => {
  images.value.normal = null;
  if (newValue) {
    nextTick()
      .then(() => {
        images.value.normal = newValue
      }).catch((e) => {
        console.error(e);
      });
  }
});

const toggleAnimation = () => {
  switch (playerStore.currentVinylAnimation) {
    case null:
      playerStore.setCurrentVinylAnimation("cassete");
      break;
    case "cassete":
      playerStore.setCurrentVinylAnimation("vinyl");
      break;
    case "vinyl":
      playerStore.setCurrentVinylAnimation(null);
      break;
  }
};

</script>

<style lang="css">
div#spieldose-sidebar-vinyl-container {
  aspect-ratio: 1;
}

img.vinyl_mini_cover {
  width: 27%;
  height: 27%;
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translateX(-50%) translateY(-50%);
  border: 0px solid #454545;
  border-radius: 100%;
}

div.spieldose-sidebar-animation-rotation-infinite {
  animation: rotation 8s linear infinite;
  z-index: 1;
}

@keyframes rotation {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(359deg);
  }
}

@-webkit-keyframes rotate {
  from {
    -webkit-transform: rotate(0deg);
  }

  to {
    -webkit-transform: rotate(359deg);
  }
}
</style>