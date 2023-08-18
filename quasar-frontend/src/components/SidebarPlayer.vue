<template>
  <div>
    <SidebarPlayerAlbumCover></SidebarPlayerAlbumCover>
    <SidebarPlayerVolumeControl></SidebarPlayerVolumeControl>

    <div id="song_info">
      <p class="song_info_track text-center text-weight-bolder" style="color: #d30320;">Won’t Get Fooled Again</p>
      <p class="song_info_album text-center">Who’s Next</p>
      <p class="song_info_artist text-center"><a href="#/app/artist/The%20Who" style="text-decoration: none;">The Who</a>
      </p>
    </div>
    <SidebarPlayerMainControls></SidebarPlayerMainControls>
    <SidebarPlayerSeekControl></SidebarPlayerSeekControl>
    <SidebarPlayerTrackActions></SidebarPlayerTrackActions>
  </div>
</template>

<script setup>
import { default as SidebarPlayerAlbumCover } from "components/SidebarPlayerAlbumCover.vue";
import { default as SidebarPlayerVolumeControl } from "components/SidebarPlayerVolumeControl.vue";
import { default as SidebarPlayerMainControls } from "components/SidebarPlayerMainControls.vue";
import { default as SidebarPlayerSeekControl } from "components/SidebarPlayerSeekControl.vue";
import { default as SidebarPlayerTrackActions } from "components/SidebarPlayerTrackActions.vue";

</script>
