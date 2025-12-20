<template>
  <div>
    <VinylDisc v-if="sidebarAlbumCoverSettingsStore.hasVinilMode" :image="images.small"
      :animated="currentPlayListsStore.playerIsPlaying" clickable @click="sidebarAlbumCoverSettingsStore.toggleMode" />
    <cassete-tape v-else-if="sidebarAlbumCoverSettingsStore.hasCassetteTapeMode"
      :animated="currentPlayListsStore.playerIsPlaying"
      :top-label="currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.artist.name"
      :id="currentPlayListsStore.currentActivePlayListItem?.file?.id ?? ''"
      :bottom-label="currentPlayListsStore.currentActivePlayListItem?.file?.trackInfo.title" clickable
      @click="sidebarAlbumCoverSettingsStore.toggleMode" />
    <StaticAlbumCoverImage v-else-if="sidebarAlbumCoverSettingsStore.hasStaticImageMode" clickable
      @click="sidebarAlbumCoverSettingsStore.toggleMode" :image="images.medium" />
    <SpieldoseLabel v-else-if="sidebarAlbumCoverSettingsStore.hasNoImage" clickable
      @click="sidebarAlbumCoverSettingsStore.toggleMode" />
  </div>
</template>

<script setup lang="ts">
  import { nextTick, ref, watch } from "vue";
  import { useCurrentPlayListsStore } from "src/stores/currentPlayLists";
  import { useSidebarAlbumCoverSettingsStore } from "src/stores/sidebarAlbumCoverSettings";

  import { default as StaticAlbumCoverImage } from "./StaticAlbumCoverImage.vue";
  import { default as VinylDisc } from "./VinylDisc.vue";
  import { default as CasseteTape } from "./CasseteTape.vue";
  import { default as SpieldoseLabel } from "./SpieldoseLabel.vue";

  const currentPlayListsStore = useCurrentPlayListsStore();

  const sidebarAlbumCoverSettingsStore = useSidebarAlbumCoverSettingsStore();

  const images = ref({
    small: currentPlayListsStore.currentActivePlayListItem?.images?.small ?? null,
    medium: currentPlayListsStore.currentActivePlayListItem?.images?.medium ?? null,
    big: currentPlayListsStore.currentActivePlayListItem?.images?.big ?? null,
  });

  watch(() => currentPlayListsStore.currentActivePlayListItem?.images?.small, (newValue) => {
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

  watch(() => currentPlayListsStore.currentActivePlayListItem?.images?.medium, (newValue) => {
    images.value.medium = null;
    if (newValue) {
      nextTick()
        .then(() => {
          images.value.medium = newValue
        }).catch((e) => {
          console.error(e);
        });
    }
  });

  watch(() => currentPlayListsStore.currentActivePlayListItem?.images?.big, (newValue) => {
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