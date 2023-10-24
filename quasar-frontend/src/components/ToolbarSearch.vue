<template>
  <q-select ref="search" dense standout use-input hide-selected class="q-mx-md" filled color="pink" :stack-label="false"
    :label="t('Search...')" v-model="searchText" :options="filteredOptions" @filter="onFilter" style="min-width: 24%;"
    :disable="disable">
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
      <q-list class="bg-grey-2 text-dark">
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
                @click="onPlayAlbum(scope.opt.id, scope.opt.label, scope.opt.artist, scope.opt.year)" />
              <q-btn class="gt-xs" size="12px" flat dense round icon="add_box" :title="t('enqueue album')"
                @click="onAppendAlbum(scope.opt.id, scope.opt.label, scope.opt.artist, scope.opt.year)" />
            </div>
          </q-item-section>
        </q-item>
        <q-item v-else-if="scope.opt.isArtist" clickable
          :to="{ name: 'artist', params: { name: scope.opt.label }, query: { mbid: scope.opt.id, tab: 'overview' } }">
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
import { useQuasar } from "quasar";
import { useI18n } from 'vue-i18n';
import { trackActions, albumActions } from '../boot/spieldose';

const $q = useQuasar();
const { t } = useI18n();

const props = defineProps({
  disable: Boolean
});

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
              return ({ isAlbum: true, id: item.mbId, label: item.title, caption: 'by  ' + item.artist.name + ' (' + item.year + ')', artist: item.artist, year: item.year, image: item.covers.small });
            }));
            filteredOptions.value = filteredOptions.value.concat(searchResults.value.artists.map((item) => {
              return ({ isMBArtist: false, isArtist: true, id: item.mbId, label: item.name, caption: 'total tracks: ' + item.totalTracks });
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
  trackActions.play(trackId);
}

function onAppendTrack(trackId) {
  trackActions.enqueue(trackId);
}

function onPlayAlbum(albumId, title, artist, year) {
  albumActions.play(
    albumId || null,
    title || null,
    artist ? artist.mbId : null,
    artist ? artist.name : null,
    year || null
  ).then((success) => {
  })
    .catch((error) => {
      switch (error.response.status) {
        default:
          // TODO: custom message
          $q.notify({
            type: "negative",
            message: t("API Error: error playing album"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
    });
}

function onAppendAlbum(albumId, title, artist, year) {
  albumActions.enqueue(
    albumId || null,
    title || null,
    artist ? artist.mbId : null,
    artist ? artist.name : null,
    year || null
  ).then((success) => {
  })
    .catch((error) => {
      switch (error.response.status) {
        default:
          // TODO: custom message
          $q.notify({
            type: "negative",
            message: t("API Error: error enqueueing album"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
    });
}

</script>
