<template>
  <q-layout>
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-3">
        <q-page-container>
          <router-view />
        </q-page-container>
      </div>
      <div class="col-md-6 col-lg-8 col-xl-9 gt-sm">
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