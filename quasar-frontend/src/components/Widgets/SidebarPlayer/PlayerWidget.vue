<template>

  <q-card>
    <q-img src="https://m.media-amazon.com/images/I/715kGo2MwhL._SL1200_.jpg"></q-img>
    <SidebarPlayerVolumeControl :default-value="0.8" :is-muted="false"></SidebarPlayerVolumeControl>
    <SidebarPlayerTrackInfo
      :track="{ title: 'Nightcall', album: { title: 'OutRun', artist: { name: 'Kavinsky' } }, artist: { name: 'Kavinsky' } }"
      :radio-station="null">
    </SidebarPlayerTrackInfo>
    <SidebarPlayerMainControls :player-status="playing"></SidebarPlayerMainControls>
    <SidebarPlayerSeekControl :current-element-time-data="{ currentTime: 20, duration: 187 }"></SidebarPlayerSeekControl>
  </q-card>
</template>

<script setup>
import { default as SidebarPlayerVolumeControl } from './SidebarPlayerVolumeControl.vue';
import { default as SidebarPlayerTrackInfo } from './SidebarPlayerTrackInfo.vue';
import { default as SidebarPlayerMainControls } from './SidebarPlayerMainControls.vue';
import { default as SidebarPlayerSeekControl } from './SidebarPlayerSeekControl.vue';
</script>
