<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container class="bg-grey-3">
      <q-header>
        <q-toolbar class="bg-purple text-white shadow-2 rounded-borders">
      <q-btn flat label="Homepage" />
      <q-space />

      <!--
        notice shrink property since we are placing it
        as child of QToolbar
      -->
      <q-tabs v-model="tab" shrink>
        <q-tab name="tab1" label="Tab 1" />
        <q-tab name="tab2" label="Tab 2" />
        <q-tab name="tab3" label="Tab 3" />
      </q-tabs>
    </q-toolbar>
      </q-header>
      <!--
      <q-header elevated>
        <q-toolbar class="bg-pink text-white">
          <q-btn flat round dense icon="menu" class="q-mr-sm" />
          <q-avatar>
            <img src="https://cdn.quasar.dev/logo-v2/svg/logo-mono-white.svg">
          </q-avatar>
          <q-toolbar-title>Spieldose</q-toolbar-title>
          <q-btn flat round dense icon="whatshot" />
        </q-toolbar>
        <q-toolbar class="bg-grey-10 text-white">
          <q-img src="images/vinyl.png" width="90px" height="90px" />
          <q-toolbar-title>
            <div class="row">
              <div class="col">
                <h5>Title<br>Artist</h5>
              </div>
              <div class="col"></div>
              <div class="q-pa-md q-gutter-sm text-center">
                <q-btn round dense size="md" :disable="disabled || !allowSkipPrevious" @click="onSkipPrevious"><q-icon
                    name="skip_previous" title="Toggle navigation menu"></q-icon></q-btn>
                <q-btn round dense size="lg" :disable="disabled || !allowPlay" @click="onPlay" class="q-mx-md"><q-icon
                    :name="isPlaying ? 'pause' : 'play_arrow'" title="Toggle navigation menu"
                    :class="{ 'text-pink-6': isPlaying }"></q-icon></q-btn>
                <q-btn round dense size="md" :disable="disabled || !allowSkipNext" @click="onSkipNext"><q-icon
                    name="skip_next" title="Toggle navigation menu"></q-icon></q-btn>
              </div>
            </div>
          </q-toolbar-title>
        </q-toolbar>
      </q-header>
      -->
      <div class="row q-gutter-lg q-pa-lg">
        <div class="col" style="width: 400px; max-width: 400px;">
          <leftSidebar></leftSidebar>
        </div>
        <div class="col">
          <router-view />
        </div>
      </div>
    </q-page-container>
  </q-layout>
</template>

<script setup>

import { usePlayer } from 'stores/player';
import { default as leftSidebar } from 'components/AppLeftSidebar.vue';

const player = usePlayer();
player.create();

</script>
