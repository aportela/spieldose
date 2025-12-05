<template>
  <q-card class="overflow-hidden">
    <AlbumCover />
    <!--
    <div style="width: 100%; height: 200px;">
      <SidebarPlayerAnalogVuMeter></SidebarPlayerAnalogVuMeter>
    </div>
    -->
    <SpectrumAnalyzer v-if="miniSpectrumAnalyzerSettings.isVisible" />
    <VolumeControl />
    <TrackInfo />
    <MainControls />
    <SeekControl />
    <TrackActions />
  </q-card>
</template>

<script setup lang="ts">
import { useMiniSpectrumAnalyzerSettingsStore } from "src/stores/miniSpectrumAnalyzerSettings";
import { default as AlbumCover } from "./Player/AlbumCover.vue";
import { default as SpectrumAnalyzer } from "./Player/SpectrumAnalyzer.vue";
import { default as VolumeControl } from "./Player/VolumeControl.vue";
import { default as TrackInfo } from "./Player/TrackInfo.vue";
import { default as MainControls } from "./Player/MainControls.vue";
import { default as SeekControl } from "./Player/SeekControl.vue";
import { default as TrackActions } from "./Player/TrackActions.vue";

const miniSpectrumAnalyzerSettings = useMiniSpectrumAnalyzerSettingsStore();

</script>