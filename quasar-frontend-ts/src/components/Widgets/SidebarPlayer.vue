<template>
  <q-card class="overflow-hidden">
    <AlbumCover />
    <!--
    <div style="width: 100%; height: 200px;">
      <SidebarPlayerAnalogVuMeter></SidebarPlayerAnalogVuMeter>
    </div>
    -->
    <!--
    <SidebarPlayerSpectrumAnalyzer v-if="miniSpectrumAnalyzerSettings.isVisible" />
    <SidebarPlayerVolumeControl />
    <SidebarPlayerTrackInfo />
    <SidebarPlayerMainControls />
    <SidebarPlayerSeekControl />
    <SidebarPlayerTrackActions />
    -->
  </q-card>
</template>

<script setup lang="ts">
import { default as AlbumCover } from "./Player/AlbumCover.vue";

/*
import { default as SidebarPlayerAlbumCover } from "./SidebarPlayerAlbumCover.vue";
import { default as SidebarPlayerSpectrumAnalyzer } from "./SidebarPlayerSpectrumAnalyzer.vue";
import { default as SidebarPlayerAnalogVuMeter } from "./SidebarPlayerAnalogVuMeter.vue";
import { default as SidebarPlayerVolumeControl } from './SidebarPlayerVolumeControl.vue';
import { default as SidebarPlayerTrackInfo } from './SidebarPlayerTrackInfo.vue';
import { default as SidebarPlayerMainControls } from './SidebarPlayerMainControls.vue';
import { default as SidebarPlayerSeekControl } from './SidebarPlayerSeekControl.vue';
import { default as SidebarPlayerTrackActions } from "./SidebarPlayerTrackActions.vue";


import { useMiniSpectrumAnalyzerSettingsStore } from "src/stores/miniSpectrumAnalyzerSettings";

const miniSpectrumAnalyzerSettings = useMiniSpectrumAnalyzerSettingsStore();
*/
</script>