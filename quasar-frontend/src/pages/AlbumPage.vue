<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <q-breadcrumbs-el icon="album" :label="t('Album')" />
    </q-breadcrumbs>
    <q-card-section>
      <div>
        <div style="float: left; margin-right: 2em;">
          <q-img :src="album.covers.normal" width="400px" height="400px" spinner-color="pink" v-if="album.covers.normal"
            :img-style="{ 'text-align': 'left' }"></q-img>
          <q-img v-else src="images/vinyl.png" alt="Vinyl" width="400px" height="400px" spinner-color="pink" />
        </div>
        <div style="padding-top: 100px">
          <div v-if="loading">
              <q-spinner size="120px" color="pink" class="q-ml-xl"></q-spinner>
          </div>
          <div v-else>
            <p class="text-subtitle2">{{ t('Album') }}</p>
            <p class="text-h2 text-weight-bolder">{{ album.title }}</p>
            <p class="text-subtitle2">{{ album.artist.name }} - {{ album.year }} - {{ totalTracks }} {{ t('tracks') }}, {{
              formatSecondsAsTime(Math.round(totalLengthInSeconds)) }}</p>
            <p><q-icon name="play_arrow" class="cursor-pointer" size="xl" :title="t('play album')"
                @click="onPlayAlbum"></q-icon> <q-icon name="add_box" class="cursor-pointer" size="xl"
                :title="t('enqueue album')" @click="onEnqueueAlbum"></q-icon></p>
            </div>
        </div>
      </div>
      <p style="clear: both;"></p>
      <div class="q-mt-md row q-col-gutter-lg" v-if="album.media?.length">
        <div :class="{ 'col-6': album.media?.length > 1, 'col-12': album.media?.length <= 1 }"
          v-for="media, index in album.media" :key="index">
          <q-markup-table>
            <caption v-if="album.media?.length > 1" class="q-pa-md"><q-icon name="album" size="xs"></q-icon> {{ t("Disc") }} {{ index
              + 1 }}</caption>
            <thead>
              <tr>
                <th class="text-left" style="width: 1em;"
                  v-if="spieldoseStore && spieldoseStore.isCurrentPlaylistElementATrack"></th>
                <th class="text-left" style="width: 2em;">#</th>
                <th class="text-left">{{ t('Title') }}</th>
                <th class="text-left" style="width: 5em;"><q-icon name="schedule" size="xs"></q-icon></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="track in media.tracks" :key="track.mbId">
                <td v-if="spieldoseStore && spieldoseStore.isCurrentPlaylistElementATrack">
                  <q-icon :name="currentElementRowIcon" size="md" :color="!spieldoseStore.isStopped ? 'pink' : 'dark'"
                    class="cursor-pointer" v-if="currentTrackId == track.id" @click="onPauseResume"></q-icon>
                    <q-icon :name="currentElementRowIcon" size="md" color="white" style="opacity: 0" v-else></q-icon>
                </td>
                <td>{{ track.position }}</td>
                <td>
                  <q-icon name="play_arrow" class="cursor-pointer q-mr-sm" size="sm" @click="onPlayTrack(track.id)"
                    v-if="track.id"></q-icon>
                  <q-icon name="add_box" class="cursor-pointer q-mr-sm" size="sm" @click="onEnqueueTrack(track.id)"
                    v-if="track.id"></q-icon>
                  {{ track.title }}<br><router-link
                    :to="{ name: 'artist', params: { name: track.artist.name }, query: { mbid: track.artist.mbId, tab: 'overview' } }">{{
                      track.artist.name }}</router-link>
                </td>
                <td>{{ formatSecondsAsTime(Math.round(track.length / 1000)) }}</td>
              </tr>
            </tbody>
          </q-markup-table>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute } from "vue-router";
import { useI18n } from "vue-i18n";
import { useQuasar, date } from "quasar";
import { useSpieldoseStore } from "stores/spieldose";
import { api } from 'boot/axios';
import { albumActions, trackActions } from "src/boot/spieldose";


const { t } = useI18n();
const $q = useQuasar();

const route = useRoute();

const spieldoseStore = useSpieldoseStore();

const currentTrackId = computed(() => spieldoseStore.getCurrentPlaylistTrackId);

const currentElementRowIcon = computed(() => {
  if (spieldoseStore.isPaused) {
    return ('pause');
  } else {
    return ('play_arrow');
  }
});

const loading = ref(false);

const album = ref({
  mbId: null,
  title: null,
  artist: {
    mbId: null,
    name: null,
  },
  year: null,
  covers: [],
  media: []
});

const totalTracks = ref(0);
const totalLengthInSeconds = ref(0);
const totalMedia = ref(0);

function get(mbId, title, artistMBId, artistName, year) {
  totalTracks.value = 0;
  totalLengthInSeconds.value = 0;
  totalMedia.value = 0;
  loading.value = true;
  api.album.get(mbId, title, artistMBId, artistName, year).then((success) => {
    album.value = success.data.album;
    album.value.media.forEach((media) => {
      totalMedia.value++;
      media.tracks.forEach((track) => {
        totalTracks.value++;
        totalLengthInSeconds.value += track.length / 1000;
      })
    });
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
    switch (error.response.status) {
      default:
        $q.notify({
          type: "negative",
          message: t("API Error: fatal error"),
          caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
        });
        break;
    }
  });
}

function formatSecondsAsTime(secs, format) {
  if (secs && Number.isInteger(secs) && secs > 0) {
    var hr = Math.floor(secs / 3600);
    var min = Math.floor((secs - (hr * 3600)) / 60);
    var sec = Math.floor(secs - (hr * 3600) - (min * 60));

    if (min < 10) {
      min = '0' + min;
    }
    if (sec < 10) {
      sec = '0' + sec;
    }
    return (min + ':' + sec);
  } else {
    return ('00:00');
  }
}

function onPlayTrack(id) {
  trackActions.play(id).then((success) => {
  })
    .catch((error) => {
      switch (error.response.status) {
        default:
          $q.notify({
            type: "negative",
            message: t("API Error: error playing track"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
    });
}


function onEnqueueTrack(id) {
  trackActions.enqueue(id).then((success) => {
  })
    .catch((error) => {
      switch (error.response.status) {
        default:
          $q.notify({
            type: "negative",
            message: t("API Error: error enqueueing track"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
    });
}

function onPlayAlbum() {
  albumActions.play(
    album.value.mbId || null,
    album.value.title || null,
    album.value.artist.mbId || null,
    album.value.artist.name || null,
    album.value.year || null
  ).then((success) => {
  })
    .catch((error) => {
      switch (error.response.status) {
        default:
          $q.notify({
            type: "negative",
            message: t("API Error: error playing album"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
    });
}

function onEnqueueAlbum() {
  albumActions.enqueue(
    album.value.mbId || null,
    album.value.title || null,
    album.value.artist.mbId || null,
    album.value.artist.name || null,
    album.value.year || null
  ).then((success) => {
  })
    .catch((error) => {
      switch (error.response.status) {
        default:
          $q.notify({
            type: "negative",
            message: t("API Error: error enqueueing album"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
    });
}

function onPauseResume() {
  spieldoseStore.interact();
  if (!spieldoseStore.isPlaying) {
    spieldoseStore.play();
  } else {
    spieldoseStore.pause();
  }
}

onMounted(() => {
  get(route.query.mbId, route.params.title, route.query.artistMBId, route.query.artistName, route.query.year);
});
</script>
