<template>
  <div v-if="playerStore.currentVinylAnimation == 'rotate'" @click="toggleAnimation"
    id="spieldose-sidebar-vinyl-container" class="cursor-pointer overflow-hidden relative-position full-width"
    style="background: url(images/vinyl.png) no-repeat; background-size: cover;"
    :class="{ 'spieldose-sidebar-animation-rotation-infinite': playerStore.isPlaying }"
    :title="t('Toggle art animation')">
    <q-img v-if="images.small" :src="images.small" @error="images.small = null" :ratio="1" img-class="vinyl_mini_cover"
      no-spinner></q-img>
  </div>
  <div v-else @click="toggleAnimation" class="cursor-pointer" :title="t('Toggle art animation')">
    <q-img v-if="images.normal" :src="images.normal" @error="images.normal = null" alt="Album cover" :ratio="1"
      width="100%" spinner-color="pink" />
    <q-img v-else src="images/vinyl.png" alt="Vinyl" :ratio="1" width="100%" spinner-color="pink" />
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { usePlayerStore } from "src/stores/player";
import { useCurrentPlaylistItemStore } from "src/stores/currentPlaylistItem";

const playerStore = usePlayerStore();
const currentPlaylistItemStore = useCurrentPlaylistItemStore();

const { t } = useI18n();

const images = ref({
  normal: currentPlaylistItemStore.trackImageNormal,
  small: currentPlaylistItemStore.trackImageSmall,
});

watch(() => currentPlaylistItemStore.trackImageSmall, (newValue) => {
  images.value.small = newValue;
});

watch(() => currentPlaylistItemStore.trackImageNormal, (newValue) => {
  images.value.normal = newValue;
});

const toggleAnimation = () => {
  if (playerStore.currentVinylAnimation == 'rotate') {
    playerStore.setCurrentVinylAnimation(null);
  } else {
    playerStore.setCurrentVinylAnimation('rotate');
  }
};

</script>

<style lang="css">
div#spieldose-sidebar-vinyl-container {
  aspect-ratio: 1;
}

img.vinyl_mini_cover {
  width: 30%;
  height: 30%;
  position: absolute;
  left: 50.5%;
  top: 50.5%;
  transform: translateX(-50%) translateY(-50%);
  border: 1px solid #454545;
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
