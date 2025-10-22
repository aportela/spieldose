<template>
  <q-layout>
    <div class="row">
      <div
        class="col-xs-10 offset-xs-1 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-4 offset-lg-0 col-xl-3 offset-xl-0 desktop-only justify-center q-pa-xl">
        <q-page-container>
          <router-view />
        </q-page-container>
      </div>
      <div class="gt-md col-lg-8 col-xl-9">
        <TileAlbumImages></TileAlbumImages>
      </div>
    </div>
  </q-layout>
</template>

<script setup>
import { useI18n } from "vue-i18n";
import { default as TileAlbumImages } from "components/TileAlbumImages.vue";

const { t } = useI18n();

</script>