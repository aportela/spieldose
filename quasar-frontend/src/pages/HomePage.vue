<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <q-breadcrumbs-el icon="analytics" :label="t('Home')" />
    </q-breadcrumbs>
    <div class="row q-col-gutter-md">
      <div class="col-3">
        <DiscoverArtistWidget discover-type-new :rows="3" :columns="3" />
      </div>
      <div class="col-3">
        <DiscoverArtistWidget discover-type-random :rows="3" :columns="3" />
      </div>
      <div class="col-3">
        <DiscoverArtistWidget discover-type-featured :rows="3" :columns="3" />
      </div>
      <div class="col-3">
        <DiscoverArtistWidget discover-type-recommended :rows="3" :columns="3" />
      </div>
    </div>

    <q-separator class="q-my-lg" />

    <div class="row q-col-gutter-md">
      <div class="col-3">
        <DiscoverAlbumWidget discover-type-new :rows="3" :columns="3" />
      </div>
      <div class="col-3">
        <DiscoverAlbumWidget discover-type-random :rows="3" :columns="3" />
      </div>
      <div class="col-3">
        <DiscoverAlbumWidget discover-type-featured :rows="3" :columns="3" />
      </div>
      <div class="col-3">
        <DiscoverAlbumWidget discover-type-recommended :rows="3" :columns="3" />
      </div>
    </div>
  </q-card>
</template>

<script setup>

import { useI18n } from "vue-i18n";

import { default as DiscoverArtistWidget } from "src/components/Widgets/DiscoverArtistWidget.vue";
import { default as DiscoverAlbumWidget } from "src/components/Widgets/DiscoverAlbumWidget.vue";

const { t } = useI18n();

</script>
