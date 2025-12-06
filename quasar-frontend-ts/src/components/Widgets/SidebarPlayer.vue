<template>
  <q-card class="overflow-hidden">
    <AlbumCover />
    <div style="width: 100%; height: 200px;" v-if="miniAnalogVumeterSettingsStore.visible">
      <SidebarAnalogVumeter />
    </div>
    <SidebarSpectrumAnalyzer v-if="sidebarMiniSpectrumAnalyzerSettingsStore.isVisible" />
    <VolumeControl />
    <TrackInfo />
    <MainControls />
    <SeekControl />
    <TrackActions />
  </q-card>
</template>

<script setup lang="ts">
import { useSidebarMiniSpectrumAnalyzerSettingsStore } from "src/stores/sidebarMiniSpectrumAnalyzerSettings";
import { useMiniAnalogVumeterSettingsStore } from "src/stores/miniAnalogVumeterSettings";
import { default as AlbumCover } from "./Player/AlbumCover.vue";
import { default as SidebarSpectrumAnalyzer } from "./Visualizations/SidebarSpectrumAnalyzer.vue";
import { default as SidebarAnalogVumeter } from "./Visualizations/SidebarAnalogVumeter.vue";
import { default as VolumeControl } from "./Player/VolumeControl.vue";
import { default as TrackInfo } from "./Player/TrackInfo.vue";
import { default as MainControls } from "./Player/MainControls.vue";
import { default as SeekControl } from "./Player/SeekControl.vue";
import { default as TrackActions } from "./Player/TrackActions.vue";


const miniAnalogVumeterSettingsStore = useMiniAnalogVumeterSettingsStore();
const sidebarMiniSpectrumAnalyzerSettingsStore = useSidebarMiniSpectrumAnalyzerSettingsStore();

</script>