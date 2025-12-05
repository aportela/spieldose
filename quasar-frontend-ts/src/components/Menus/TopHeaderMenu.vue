<template>
  <q-btn-group flat class="q-ml-md" v-if="visible">
    <q-btn stack v-for="item in menuItems" size="md" no-caps :icon="item.icon" :key="item.text" :to="item.routeName">{{
      item.text
      }}</q-btn>
  </q-btn-group>
</template>

<script setup lang="ts">

interface TopHeaderMenuProps {
  visible: boolean;
};

defineProps<TopHeaderMenuProps>();

const menuItems = [
  { icon: 'home', text: "Index", routeName: 'index' },
  { icon: 'search', text: "Search", routeName: 'search' },

  {
    icon: 'analytics',
    text: 'Dashboard',
    routeName: 'dashboard'
  },

  {
    icon: 'list_alt',
    text: 'Current playlist',
    routeName: 'currentPlaylist'
  },
  {
    icon: 'person',
    text: 'Browse artists',
    routeName: 'artists'
  },
  /*
  {
    icon: 'search',
    text: 'Search',
    routeName: 'search'
  },
  */
  {
    icon: 'album',
    text: 'Browse albums',
    routeName: 'albums'
  },
  {
    icon: 'folder_open',
    text: 'Browse paths',
    routeName: 'paths'
  },

  {
    icon: 'list',
    text: 'Browse playlists',
    routeName: 'playlists'
  },
  {
    icon: 'radio',
    text: 'Browse radio stations',
    routeName: 'radioStations'
  },
  { icon: 'account_circle', text: "My profile", routeName: 'profile' },
];
</script>