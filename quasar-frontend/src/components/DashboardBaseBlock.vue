<template>
  <q-card class="my-card">
    <q-card-section class="bg-grey-3 text-black">
      <div class="text-h6"><q-icon :name="icon" class="cursor-pointer q-mr-sm"></q-icon>{{ title }}<q-icon name="refresh"
          class="cursor-pointer"></q-icon></div>
    </q-card-section>
    <q-card-section class="bg-white text-black">
      <slot name="body">
        <ol class="pl-5 is-size-6-5" v-if="items.length > 0">
          <li class="is-size-6-5" v-for="item in items" :key="item.id">
            <i class="cursor-pointer fa-fw fa fa-play"
              ></i>
            <i class="cursor-pointer fa-fw fa fa-plus-square mr-1"
              ></i>
            <span>{{ item.title }}</span>
            <span v-if="item.artist"> / <router-link :to="{ name: 'artist', params: { name: item.artist } }"
                >{{ item.artist }}</router-link></span>
            <span v-if="true || showPlayCount"> ({{ item.playCount }} plays)</span>
          </li>
          <!--
          <li class="is-size-6-5" v-if="isTopAlbumsType" v-for="item in items">
            <span>{{ item.album }}</span>
            <span v-if="item.artist"> / <router-link :to="{ name: 'artist', params: { name: item.artist } }"
                >{{ item.artist }}</router-link></span>
          </li>
          <li class="is-size-6-5" v-if="isTopArtistsType" v-for="item in items">
            <router-link :to="{ name: 'artist', params: { name: item.artist } }"
              >{{ item.artist }}</router-link>
            <span v-if="showPlayCount"> ({{ item.playCount }} plays </span>
          </li>
          <li class="is-size-6-5" v-if="isTopGenresType" v-for="item in items">
            <span>{{ item.genre }}</span>
            <span v-if="showPlayCount"> ({{ item.playCount }} plays }})</span>
          </li>
          -->
        </ol>
      </slot>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref } from 'vue'
import { api } from 'boot/axios'

const props = defineProps({
  icon: {
    type: String,
    required: true
  },
  title: {
    type: String,
    required: true
  }
})

const loading = ref(false);

const items = ref([]);

function refresh() {
  loading.value = true;

  api.metrics.getTopPlayedTracks().then((success) => {
    items.value = success.data.data;
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
  });
}

refresh();
</script>
