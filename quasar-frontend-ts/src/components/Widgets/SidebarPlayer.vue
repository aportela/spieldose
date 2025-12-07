<template>
  <q-card class="overflow-hidden">
    <AlbumCover />
    <div style="width: 100%; height: 200px;" v-if="sidebarAnalogVumeterSettingsStore.visible">
      <SidebarAnalogVumeter />
    </div>
    <SidebarSpectrumAnalyzer v-if="sidebarSpectrumAnalyzerSettingsStore.visible" />
    <VolumeControl />
    <TrackInfo />
    <MainControls />
    <SeekControl />
    <TrackActions />
  </q-card>
</template>

<script setup lang="ts">
import { useSidebarSpectrumAnalyzerSettingsStore } from "src/stores/sidebarSpectrumAnalyzerSettings";
import { useSidebarAnalogVumeterSettingsStore } from "src/stores/sidebarAnalogVumeterSettings";
import { default as AlbumCover } from "./Visualizations/AlbumCover.vue";
import { default as SidebarSpectrumAnalyzer } from "./Visualizations/SidebarSpectrumAnalyzer.vue";
import { default as SidebarAnalogVumeter } from "./Visualizations/SidebarAnalogVumeter.vue";
import { default as VolumeControl } from "./Player/VolumeControl.vue";
import { default as TrackInfo } from "./Player/TrackInfo.vue";
import { default as MainControls } from "./Player/MainControls.vue";
import { default as SeekControl } from "./Player/SeekControl.vue";
import { default as TrackActions } from "./Player/TrackActions.vue";


const sidebarAnalogVumeterSettingsStore = useSidebarAnalogVumeterSettingsStore();
const sidebarSpectrumAnalyzerSettingsStore = useSidebarSpectrumAnalyzerSettingsStore();

</script>