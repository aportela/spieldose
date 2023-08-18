<template>
  <div>
    <SidebarPlayerAlbumCover></SidebarPlayerAlbumCover>
    <SidebarPlayerVolumeControl></SidebarPlayerVolumeControl>
    <SidebarPlayerTrackInfo></SidebarPlayerTrackInfo>
    <SidebarPlayerMainControls></SidebarPlayerMainControls>
    <SidebarPlayerSeekControl></SidebarPlayerSeekControl>
    <SidebarPlayerTrackActions></SidebarPlayerTrackActions>
  </div>
</template>

<script setup>
import { default as SidebarPlayerAlbumCover } from "components/SidebarPlayerAlbumCover.vue";
import { default as SidebarPlayerVolumeControl } from "components/SidebarPlayerVolumeControl.vue";
import { default as SidebarPlayerTrackInfo } from "components/SidebarPlayerTrackInfo.vue";
import { default as SidebarPlayerMainControls } from "components/SidebarPlayerMainControls.vue";
import { default as SidebarPlayerSeekControl } from "components/SidebarPlayerSeekControl.vue";
import { default as SidebarPlayerTrackActions } from "components/SidebarPlayerTrackActions.vue";

</script>
