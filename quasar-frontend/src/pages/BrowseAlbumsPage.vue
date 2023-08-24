<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="album" label="Browse albums" />
      </q-breadcrumbs>

      <q-card-section v-if="albums">

        <div class="q-pa-lg flex flex-center" v-if="totalPages > 1">
                <q-pagination v-model="currentPageIndex" color="dark"
                  :max="totalPages" :max-pages="5" boundary-numbers direction-links
                  boundary-links @update:model-value="onPaginationChanged" :disable="loading" />
              </div>
        <div class="q-gutter-md row items-start" >


            <router-link style="text-decoration:none" :to="{ name: 'album', params: { title: album.title }}" v-for="album in albums" :key="album">
            <q-img src="images/vinyl.png" width="250px" height="250px" fit="cover">
            </q-img>
            <div><span class="text-dark">{{ album.title }}</span>
                <p v-if="album.artist.name || album.year"><span v-if="album.artist.name"><span class="text-dark">by </span>
                  <router-link style="text-decoration:none" :to="{ name: 'artist', params: { name: album.artist.name }}">
                  {{ album.artist.name }}</router-link>
                </span><span class="text-dark" v-if="album.year"> ({{  album.year }})</span></p>
              </div>
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
const albums = ref([]);

const totalPages = ref(10);
const currentPageIndex  =ref(3);

function search() {
  loading.value = true;
  api.album.search(currentPageIndex.value, 32, { }).then((success) => {
    albums.value = success.data.data.items;
    totalPages.value = success.data.data.pager.totalPages;
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
  });
}

function onPaginationChanged(pageIndex) {
  currentPageIndex.value = pageIndex;
  search();
}

search();

</script>
