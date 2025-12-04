<template>
  <q-layout>
    <div class="row">
      <div class="col-12 col-lg-8 col-md-12 col-sm-12 slogan-background-image-cover flex flex-center">
        <div class="q-pa-xl text-center text-white">
          <h2 class="text-h2 text-weight-bolder q-mb-md">{{ t("Welcome to HomeDocs") }}</h2>
          <h4 class="text-h4 text-grey-4">{{ t("Where your documents find their place.") }}</h4>
        </div>
      </div>
      <div class="col-12 col-lg-4">
        <q-page-container>
          <router-view />
        </q-page-container>
      </div>
    </div>
  </q-layout>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";

const { t } = useI18n();
</script>

<style lang="css" scoped>
.slogan-background-image-cover {
  /* image credits: https://www.pexels.com/photo/keyboard-and-mouse-on-beige-background-3184460/ */
  background-image: linear-gradient(135deg, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.8) 100%), url('/images/pexels-photo-3184460.jpg');
  background-size: cover;
  background-position: center;
  filter: grayscale(100%);
}
</style>
