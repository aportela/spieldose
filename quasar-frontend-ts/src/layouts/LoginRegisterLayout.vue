<template>
  <q-layout>
    <div class="row">
      <div class="col-xs-12 col-sm-9 col-md-6 col-lg-4 col-xl-3">
        <q-page-container>
          <router-view />
        </q-page-container>
      </div>
      <div class="col-sm-3 col-md-6 col-lg-8 col-xl-9 gt-xs">
        <TileAlbumImages></TileAlbumImages>
      </div>
    </div>
  </q-layout>
</template>

<script setup lang="ts">
import { default as TileAlbumImages } from "components/Other/TileAlbumImages.vue";

</script>