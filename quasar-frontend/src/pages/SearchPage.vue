<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="search" label="Search" />
      </q-breadcrumbs>

      <q-card>
        <q-tabs v-model="tab" dense class2="text-grey" active-color2="primary" indicator-color2="primary" align="justify"
          narrow-indicator>
          <q-tab name="global" label="Global search" />
          <q-tab name="advanced" label="Advanced search" />
        </q-tabs>

        <q-separator />

        <q-tab-panels v-model="tab" animated>
          <q-tab-panel name="global">
            <q-select ref="search" outline use-input hide-selected class="q-mx-md q-mb-lg" color="white"
              :stack-label="false" label="Search..." v-model="searchText" style="width: 100%" @filter="onFilter">
              <template v-slot:no-option v-if="loading">
                <q-item>
                  <q-item-section>
                    <div class="text-center">
                      <q-spinner-pie color="grey-5" size="24px" />
                    </div>
                  </q-item-section>
                </q-item>
              </template>
            </q-select>
          </q-tab-panel>

          <q-tab-panel name="advanced">
            <form>
              <q-input outlined v-model="text" label="Track title" placeholder="type text condition" hint="Search on track title" dense clearable
        clear-icon="close"/>
 <q-input outlined v-model="text" label="Artist name" placeholder="type text condition" hint="Search on artist name" dense clearable
        clear-icon="close"/>
 <q-input outlined v-model="text" label="Album name" placeholder="type text condition" hint="Search on album name" dense clearable
        clear-icon="close"/>
        <q-input outlined v-model="text" label="Album artist" placeholder="type text condition" hint="Search on album artist" dense clearable
        clear-icon="close"/>
        <q-input outlined v-model="text" label="Year" placeholder="type year condition" hint="Search on track year" dense clearable
        clear-icon="close"/>
            </form>
          </q-tab-panel>

        </q-tab-panels>
      </q-card>

      <q-btn v-if="tracks && tracks.length > 0" @click="onSendPlaylist">Send to playlist</q-btn>
      <q-markup-table flat bordered v-if="tracks && tracks.length > 0">
        <thead>
          <tr class="bg-grey-2 text-grey-10">
            <th class="text-left">Title</th>
            <th class="text-left">Artist</th>
            <th class="text-left">Album Artist</th>
            <th class="text-left">Album</th>
            <th class="text-right">Album Track nº</th>
            <th class="text-right">Year</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="track, index in tracks" :key="track.id" class="non-selectable cursor-pointer"
            :class="{ 'bg-pink text-white': currentTrackIndex == index }" @click="currentTrackIndex = index">
            <td class="text-left">{{ track.title }}</td>
            <td class="text-left"><router-link v-if="track.artist"
                :class="{ 'text-white text-bold': currentTrackIndex == index }"
                :to="{ name: 'artist', params: { name: track.artist } }"><q-icon name="link" class="q-mr-sm"></q-icon>{{
                  track.artist }}</router-link></td>
            <td class="text-left"><router-link v-if="track.albumArtist"
                :class="{ 'text-white text-bold': currentTrackIndex == index }"
                :to="{ name: 'artist', params: { name: track.albumArtist } }"><q-icon name="link"
                  class="q-mr-sm"></q-icon>{{ track.albumArtist }}</router-link></td>
            <td class="text-left">{{ track.album }}<span class="is-clickable"><i class="fas fa-link ml-1"></i></span></td>
            <td class="text-right">{{ track.trackNumber }}</td>
            <td class="text-right">{{ track.year }}</td>
            <td class="text-center">
              <q-btn-group outline>
                <q-btn size="sm" color="white" text-color="grey-5" icon="north" title="Up" disabled />
                <q-btn size="sm" color="white" text-color="grey-5" icon="south" title="Down" disabled />
                <q-btn size="sm" color="white" text-color="grey-5" icon="favorite" title="Toggle favorite" disabled />
                <q-btn size="sm" color="white" text-color="grey-5" icon="download" title="Download" disabled />
              </q-btn-group>
            </td>
          </tr>
        </tbody>
      </q-markup-table>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { api } from 'boot/axios'

import { usePlayerStatusStore } from 'stores/playerStatus'
import { useCurrentPlaylistStore } from 'stores/currentPlaylist'

const currentPlaylist = useCurrentPlaylistStore();
const playerStatus = usePlayerStatusStore();

const searchText = ref(null);

const tracks = ref([]);

const loading = ref(false);

const filteredOptions = ref([]);

const tab = ref('advanced');

function onFilter(val, update) {
  if (val && val.trim().length > 0) {
    filteredOptions.value = [];
    loading.value = true;
    update(() => {
      api.track.search(0, 128, { text: val })
        .then((success) => {
          tracks.value = success.data.tracks;
          /*
          filteredOptions.value = success.data.results.tracks.map((document) => {
            return ({ id: document.id, label: document.title, caption: t("Fast search caption", { creation: date.formatDate(document.createdOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss'), attachmentCount: document.fileCount }) });
          });
          */
          loading.value = false;
          return;
        })
        .catch((error) => {
          loading.value = false;
          /*
          $q.notify({
            type: "negative",
            message: t("API Error: fatal error"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          */
          return;
        });
    });
  } else {
    update(() => {
      filteredOptions.value = [];
    });
    return;
  }
}

function onSendPlaylist() {
  currentPlaylist.saveTracks(tracks.value);
}

</script>
