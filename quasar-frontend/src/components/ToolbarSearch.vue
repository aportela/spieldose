<template>
  <q-select ref="search" dense standout use-input hide-selected class="q-mx-md" filled color="pink" :stack-label="false"
    :label="t('Search...')" v-model="searchText" :options="filteredOptions" @filter="onFilter" style="min-width: 24%;">
    <template v-slot:no-option v-if="searching">
      <q-item>
        <q-item-section>
          <div class="text-center">
            <q-spinner color="pink" size="32px" />
          </div>
        </q-item-section>
      </q-item>
    </template>
    <template v-slot:option="scope">
      <q-list class="bg-grey-2 text-dark" >
        <q-item v-if="scope.opt.isTrack">
          <q-item-section avatar top>
            <q-icon name="music_note" color="black" size="34px" />
          </q-item-section>
          <q-item-section top class="col-1 gt-sm">
            <q-item-label class="q-mt-sm">Track</q-item-label>
          </q-item-section>
          <q-item-section top>
            <q-item-label lines="1">
              <span class="text-weight-medium">{{ scope.opt.label }}</span>
            </q-item-label>
            <q-item-label caption lines="1">
              {{ scope.opt.caption }}
            </q-item-label>
          </q-item-section>
          <q-item-section top side>
            <div class="text-grey-8 q-gutter-xs">
              <q-btn class="gt-xs" size="12px" flat dense round icon="play_arrow" :title="t('play track')"
                @click="onPlayTrack(scope.opt.id)" />
              <q-btn class="gt-xs" size="12px" flat dense round icon="add_box" :title="t('enqueue track')"
                @click="onAppendTrack(scope.opt.id)" />
            </div>
          </q-item-section>
          <q-separator></q-separator>
        </q-item>
        <q-item v-else-if="scope.opt.isAlbum">
          <q-item-section avatar top>
            <q-img :src="scope.opt.image" v-if="scope.opt.image" style="width: 34px; height: 34px;">
              <template v-slot:error>
                <q-icon name="album" color="black" size="34px"></q-icon>
              </template>
            </q-img>
            <q-icon name="album" color="black" size="34px" v-else />
          </q-item-section>
          <q-item-section top class="col-1 gt-sm">
            <q-item-label class="q-mt-sm">Album</q-item-label>
          </q-item-section>
          <q-item-section top>
            <q-item-label lines="1">
              <span class="text-weight-medium">{{ scope.opt.label }}</span>
            </q-item-label>
            <q-item-label caption lines="2">
              {{ scope.opt.caption }}
              <br>
              total tracks: 13
            </q-item-label>
          </q-item-section>
          <q-item-section top side>
            <div class="text-grey-8 q-gutter-xs">
              <q-btn class="gt-xs" size="12px" flat dense round icon="play_arrow" :title="t('play album')"
                @click="onPlayAlbum(scope.opt.id, scope.opt.label, scope.opt.albumArtist, scope.opt.year)" />
              <q-btn class="gt-xs" size="12px" flat dense round icon="add_box" :title="t('enqueue album')"
                @click="onAppendAlbum(scope.opt.id, scope.opt.label, scope.opt.albumArtist, scope.opt.year)" />
            </div>
          </q-item-section>
        </q-item>
        <q-item v-else-if="scope.opt.isArtist" clickable :to="{ name: 'artist', params: { name: scope.opt.label } }">
          <q-item-section avatar top>
            <q-icon name="person" color="black" size="34px" />
          </q-item-section>
          <q-item-section top class="col-1 gt-sm">
            <q-item-label class="q-mt-sm">Artist</q-item-label>
          </q-item-section>
          <q-item-section top>
            <q-item-label lines="1">
              <span class="text-weight-medium">{{ scope.opt.label }}</span>
            </q-item-label>
            <q-item-label caption lines="1">
              {{ scope.opt.caption }}
            </q-item-label>
          </q-item-section>
          <q-item-section top side>
            <div class="text-grey-8 q-gutter-xs">
              <q-icon class="gt-xs" size="sm" flat dense round name="link"></q-icon>
            </div>
          </q-item-section>
        </q-item>
      </q-list>
    </template>
  </q-select>
</template>

<script setup>
import { ref } from "vue";
import { api } from 'boot/axios';
import { useI18n } from 'vue-i18n';
import { i18n } from "src/boot/i18n";
import { trackActions, albumActions } from '../boot/spieldose';

const { t } = useI18n();

const searchText = ref(null);

const filteredOptions = ref([]);

const searching = ref(false);

const searchResults = ref([]);

function onFilter(val, update) {
  if (val && val.trim().length > 0) {
    if (!searching.value) {
      filteredOptions.value = [];
      searching.value = true;
      update(() => {
        api.globalSearch.search({ text: val }, 1, 3, false, '', 'ASC')
          .then((success) => {
            searchResults.value = success.data.data;
            // TODO add internal id for searching
            filteredOptions.value = [];
            filteredOptions.value = filteredOptions.value.concat(searchResults.value.tracks.map((item) => {
              return ({ isTrack: true, id: item.id, label: item.title, caption: t('fastSearchResultCaption', { artistName: item.artist.name, albumTitle: item.album.title, albumYear: item.album.year }) });
            }));
            filteredOptions.value = filteredOptions.value.concat(searchResults.value.albums.map((item) => {
              return ({ isAlbum: true, id: item.mbId, label: item.title, caption: 'by  ' + item.artist.name + ' (' + item.year + ')', albumArtist: item.artist.name, year: item.year, image: item.covers.small });
            }));
            filteredOptions.value = filteredOptions.value.concat(searchResults.value.artists.map((item) => {
              return ({ isArtist: true, id: item.mbId, label: item.name, caption: 'total tracks: ' + item.totalTracks });
            }));
            searching.value = false;
            return;
          })
          .catch((error) => {
            searching.value = false;
            $q.notify({
              type: "negative",
              message: t("API Error: fatal error"),
              caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
            });
            return;
          });
      });
    }
  } else {
    update(() => {
      filteredOptions.value = [];
    });
    return;
  }
}

function onPlayTrack(trackId) {
  const element = searchResults.value.tracks.find((element) => element.id == trackId);
  if (element) {
    trackActions.play(element);
  }
}

function onAppendTrack(trackId) {
  const element = searchResults.value.tracks.find((element) => element.id == trackId);
  if (element) {
    trackActions.enqueue(element);
  }
}

function onPlayAlbum(albumId, title, artistName, year) {
  let filter = {};
  if (albumId) {
    filter = { albumMbId: albumId };
  } else {
    // TODO: check filter
    filter = {
      albumTitle: title || null,
      artistName: artistName || null,
      year: year || null
    };
  }
  api.track.search(filter, 1, 0, false, 'trackNumber', 'ASC').then((success) => {
    trackActions.play(success.data.data.items.map((item) => { return ({ track: item }); }));
  }).catch((error) => {
    // TODO: on error
  });
}

function onAppendAlbum(albumId, title, artistName, year) {
  let filter = {};
  if (albumId) {
    filter = { albumMbId: albumId };
  } else {
    // TODO: check filter
    filter = {
      albumTitle: title || null,
      artistName: artistName || null,
      year: year || null
    };
  }
  api.track.search(filter, 1, 0, false, 'trackNumber', 'ASC').then((success) => {
    trackActions.enqueue(success.data.data.items.map((item) => { return ({ track: item }); }));
  }).catch((error) => {
    // TODO: on error
  });
}

</script>
