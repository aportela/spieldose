<template>
  <router-link :to="{ name: 'artist', params: { name: name }, query: { mbid: mbId, tab: 'overview' } }">
    <q-img img-class="sp-artist-image-filter" :src="image || '#'" width="250px" height="250px" fit="cover">
      <div class="absolute-bottom text-subtitle1 text-center">
        {{ name }}
        <p class="text-caption q-mb-none">{{ totalTracks }} {{ t(totalTracks > 1 ? "tracks" : "track") }}</p>
      </div>
      <template v-slot:loading>
        <div class="absolute-full flex flex-center bg-grey-3 text-dark">
          <q-spinner color="pink" size="xl" />
          <div class="absolute-bottom text-subtitle1 text-center bg-grey-5 q-py-md">
            {{ name }}
            <p class="text-caption q-mb-none">{{ totalTracks }} {{ t(totalTracks > 1 ? "tracks" : "track") }}</p>
          </div>
        </div>
      </template>
      <template v-slot:error>
        <div class="absolute-full flex flex-center bg-grey-3 text-dark">
          <div class="absolute-bottom text-subtitle1 text-center bg-grey-5 q-py-md">
            {{ name }}
            <p class="text-caption q-mb-none">{{ totalTracks }} {{ t(totalTracks > 1 ? "tracks" : "track") }}</p>
          </div>
        </div>
      </template>
    </q-img>
  </router-link>
</template>

<style>
img.sp-artist-image-filter {
  -webkit-filter: grayscale(100%) blur(4px) opacity(0.5);
  /* Safari 6.0 - 9.0 */
  filter: grayscale(100%) blur(4px) opacity(0.5);
  transition: filter 0.2s ease-in;
}

img.sp-artist-image-filter:hover {
  -webkit-filter: none;
  /* Safari 6.0 - 9.0 */
  filter: none;
  transition: filter 0.2s ease-out;

}
</style>

<script setup>
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps(['mbId', 'name', 'image', 'totalTracks']);

</script>
