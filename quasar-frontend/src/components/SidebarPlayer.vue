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
    <div id="player_controls">
      <ul class="list list--buttons">
        <li><a href="#" class="list__link"><q-icon name="skip_previous"></q-icon></a></li>
        <li><a href="#" class="list__link" style=""><q-icon name="play_arrow"></q-icon></a></li>
        <li><a href="#" class="list__link"><q-icon name="skip_next"></q-icon></a></li>
      </ul>
    </div>
    <SidebarPlayerSeekControl></SidebarPlayerSeekControl>
    <SidebarPlayerTrackActions></SidebarPlayerTrackActions>
  </div>
</template>

<style>
div#player_controls {
  padding-bottom: 1rem;
}

.list--buttons {
  align-items: center;
  justify-content: center;
}

.list {
  display: flex;
  margin: 0;
  padding: 0;
  list-style-type: none;
}

ul {
  list-style: none;
  list-style-type: none;
}

.list--buttons li:first-of-type a,
.list--buttons li:last-of-type a {
  font-size: 0.95rem;
  color: #212121;
  opacity: 0.5;
}

.list--buttons li:nth-of-type(n+2) {
  margin-left: 1.25rem;
}

.list--buttons a {
  padding-top: 0.45rem;
  padding-right: 0.75rem;
  padding-bottom: 0.45rem;
  padding-left: 0.75rem;
  font-size: 1rem;
  border-radius: 50%;
  box-shadow: 0 3px 6px rgba(33, 33, 33, 0.1), 0 3px 12px rgba(33, 33, 33, 0.15);
}

.list__link {
  transition: all 0.25s cubic-bezier(0.4, 0, 1, 1);
}

a {
  color: #485fc7;
  cursor: pointer;
  text-decoration: none;
}


div#current_track_actions {
  padding: 1rem 4rem;
}

div#current_track_actions ul {
  display: flex;
  margin: 0;
  padding: 0;
  list-style-type: none;
  justify-content: space-between;
}
</style>

<script setup>
import { default as SidebarPlayerAlbumCover } from "components/SidebarPlayerAlbumCover.vue";
import { default as SidebarPlayerVolumeControl } from "components/SidebarPlayerVolumeControl.vue";
import { default as SidebarPlayerSeekControl } from "components/SidebarPlayerSeekControl.vue";
import { default as SidebarPlayerTrackActions } from "components/SidebarPlayerTrackActions.vue";

</script>
