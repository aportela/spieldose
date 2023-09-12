<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container>
      <q-page>
        <div class="row items-center">
          <div
            class="col-xs-10 offset-xs-1 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-4 offset-lg-0 col-xl-3 offset-xl-0 desktop-only justify-center q-pa-xl">
            <CSSAnimatedAudioEqualizer></CSSAnimatedAudioEqualizer>
            <router-view />
          </div>
          <div class="gt-md col-lg-8 col-xl-9 container_tiles">
            <TileAlbumImages></TileAlbumImages>
          </div>
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { default as CSSAnimatedAudioEqualizer } from "components/CSSAnimatedAudioEqualizer.vue";
import { default as TileAlbumImages } from "components/TileAlbumImages.vue";
</script>
