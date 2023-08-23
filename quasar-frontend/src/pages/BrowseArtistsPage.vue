<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="person" label="Browse artists" />
      </q-breadcrumbs>

      <q-card-section>
        <div class="q-gutter-sm row items-start" v-if="artists">
            <router-link :to="{ name: 'artist', params: { name: artist.name }}" v-for="artist in artists" :key="artist">
            <q-img :src="artist.image || '#'" width="300px" height="300px" fit="cover">
              <div class="absolute-bottom text-subtitle1 text-center">
              {{ artist.name }}
              </div>
              <template v-slot:error>
                <div class="absolute-full flex flex-center bg-grey-3 text-dark">
                  <div class="absolute-bottom text-subtitle1 text-center bg-grey-5 q-py-md">
                    {{ artist.name }}
                  </div>
                </div>
              </template>
            </q-img>
            </router-link>
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup>

import { ref, watch, computed } from "vue";
import { api } from 'boot/axios'

const loading = ref(false);
const artists = ref([]);

function search() {
  loading.value = true;
  api.artist.search(0, 32, { }).then((success) => {
    artists.value = success.data.artists;
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
  });
}

search();

</script>
