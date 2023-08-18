<template>
  <q-page>
    <leftSidebar></leftSidebar>
    <div id="artist-header-block">
      <div id="artist-header-block-background-image"
        style="background-image: url(https://commons.wikimedia.org/w/thumb.php?f=Bob_Dylan_-_Azkena_Rock_Festival_2010_1.jpg&w=400);">
      </div>
      <div id="artist-header-block-background-overlay"></div>
      <div id="artist-header-block-content">
        <div class="q-pl-xl q-pt-xl">
          <p class="text-h3 text-bold">{{ artistName }}</p>
          <p class="text-white text-bold"><span class="text-grey"><q-icon name="groups" size="sm"
                class="q-mr-sm"></q-icon> Listeners: </span>
            <span class="text-white">0 user/s</span>
          </p>
          <p class="text-white text-bold"><span class="text-grey"><q-icon name="album" size="sm" class="q-mr-sm"></q-icon>
              Total
              plays: </span> <span class="text-white">0 times</span></p>
          <div class="row q-mt-xl">
            <div class="col-6 ">
              <div class="float-left" style="width: 96px; height:96px;">
                <q-img src="http://127.0.0.1:8081/api/2/cache/thumbnail/small/d7af7e7a3c07f69f56beaa92b3029d173e7c9d8e"
                  spinner-color="white" style="height: 96px; max-width: 96px">
                  <template v-slot:error>
                    <div class="absolute-full flex flex-center bg-grey-0">
                    </div>
                  </template>
                </q-img>
              </div>
              <p class="float-left" style="margin-left: 24px; margin-top: 10px;"><span class="text-grey">LATEST RELEASE
                  <br><strong class="text-white">Album name</strong><br><span class="text-white">1999</span></span>
              </p>
            </div>
            <div class="col-6">
              <div class="float-left" style="width: 96px; height:96px;">
                <q-img src="http://127.0.0.1:8081/api/2/cache/thumbnail/small/d7af7e7a3c07f69f56beaa92b3029d173e7c9d8e"
                  spinner-color="white" style="height: 96px; max-width: 96px">
                  <template v-slot:error>
                    <div class="absolute-full flex flex-center bg-grey-0">
                    </div>
                  </template>
                </q-img>
              </div>
              <p class="float-left" style="margin-left: 24px; margin-top: 10px;"><span class="text-grey">POPULAR
                  <br><strong class="text-white">Album name</strong><br><span class="text-white">1999</span></span>
              </p>
            </div>
          </div>
        </div>
        <q-tabs v-model="tab" class="tex-white q-mt-md">
          <q-tab name="overview" label="Overview" />
          <q-tab name="biography" label="Biography" />
          <q-tab name="similarArtists" label="Similar artists" />
          <q-tab name="albums" label="Albums" />
          <q-tab name="tracks" label="Tracks" />
          <q-tab name="stats" label="Stats" />
        </q-tabs>

      </div>

    </div>
  </q-page>
</template>

<style>
div#artist-header-block {
  overflow: hidden;
  color: #fff;
  background: rgba(24, 24, 24, 1);
  height: 400px;
  position: relative;
}

div#artist-header-block-background-image {
  background-size: cover;
  background-repeat: no-repeat;
  background-position: 50% 25%;
  width: 50%;
  height: 400px;
  position: absolute;
  top: 0;
  right: 0;
  z-index: 0;
  filter: blur(2px);
  transition: filter 0.3s ease-in;
}

div#artist-header-block-background-overlay {
  background: linear-gradient(0.25turn, rgba(24, 24, 24, 1), rgba(92, 71, 59, 0));
  width: 51%;
  height: 400px;
  position: absolute;
  top: 0;
  right: 0;
  z-index: 1;
}

div#artist-header-block-content {
  width: 50%;
  z-index: 2;
}
</style>

<script setup>
import { ref } from 'vue'
import { default as leftSidebar } from 'components/AppLeftSidebar.vue';

import { useRoute } from 'vue-router'
const route = useRoute()
const artistName = route.params.name;

const tab = ref('overview');

</script>
