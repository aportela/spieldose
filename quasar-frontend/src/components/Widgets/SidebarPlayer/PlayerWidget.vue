<template>

  <q-card>
    <q-img src="https://m.media-amazon.com/images/I/715kGo2MwhL._SL1200_.jpg" @click="refresh"></q-img>
    <SidebarPlayerVolumeControl :default-value="0.8" :is-muted="false"></SidebarPlayerVolumeControl>
    <!--
    <SidebarPlayerTrackInfo
      :track="{ title: 'Nightcall', album: { title: 'OutRun', artist: { name: 'Kavinsky' } }, artist: { name: 'Kavinsky' } }"
      :radio-station="null">
    </SidebarPlayerTrackInfo>
    <SidebarPlayerSeekControl :current-element-time-data="{ currentTime: 20, duration: 187 }">
    </SidebarPlayerSeekControl>
    -->
    <SidebarPlayerMainControls />
  </q-card>
</template>

<script setup>
import { computed, onMounted } from "vue";

import { default as SidebarPlayerVolumeControl } from './SidebarPlayerVolumeControl.vue';
import { default as SidebarPlayerTrackInfo } from './SidebarPlayerTrackInfo.vue';
import { default as SidebarPlayerMainControls } from './SidebarPlayerMainControls.vue';
import { default as SidebarPlayerSeekControl } from './SidebarPlayerSeekControl.vue';

import { useAPI } from 'src/composables/useAPI';
import { usePlayerStore } from 'src/stores/player';

const playerStore = usePlayerStore();

const { api } = useAPI();

const refresh = () => {
  api.file.getRandom().then((successResponse) => {
    playerStore.setAudioSource("/api2/file/raw/" + successResponse.data.file.id);
  })
    .catch((errorResponse) => {
      console.error(errorResponse);
    });
};

onMounted(() => {
  refresh();
});

</script>
