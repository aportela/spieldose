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
          <p class="text-subtitle2">{{ t('Album') }}</p>
          <p class="text-h2 text-weight-bolder">{{ album.title }}</p>
          <p class="text-subtitle2">{{ album.artist.name }} - {{ album.year }} - {{ totalTracks }} tracks, {{
            formatSecondsAsTime(Math.round(totalLength / 1000)) }}</p>
          <p><q-icon name="play_arrow" class="cursor-pointer" size="xl" @click="onPlayAlbum"></q-icon></p>
        </div>
      </div>
      <p style="clear: both;"></p>
      <q-markup-table class="q-mt-md" v-if="album.media?.length">
        <thead>
          <tr>
            <th class="text-left" style="width: 1em;" v-if="spieldoseStore && spieldoseStore.isCurrentPlaylistElementATrack"></th>
            <th class="text-left" style="width: 3em;" v-if="album.media.length > 1"><q-icon name="album"
                size="xs"></q-icon></th>
            <th class="text-left" style="width: 2em;">#</th>
            <th class="text-left">{{ t('Title') }}</th>
            <th class="text-left" style="width: 5em;"><q-icon name="schedule" size="xs"></q-icon></th>
          </tr>
        </thead>
        <tbody v-for="media, index in album.media" :key="index">
          <tr v-for="track in media.tracks" :key="track.mbId">
            <td v-if="spieldoseStore && spieldoseStore.isCurrentPlaylistElementATrack">
              <q-icon :name="currentElementRowIcon" size="md" color="pink" v-if="currentTrackId == track.id"></q-icon>
            </td>
            <td v-if="album.media.length > 1">{{ index + 1 }}</td>
            <td>{{ track.position }}</td>
            <td>
              <q-icon name="play_arrow" class="cursor-pointer q-mr-sm" size="sm" @click="onPlayTrack(track.id)" v-if="track.id"></q-icon>
              <q-icon name="add_box" class="cursor-pointer q-mr-sm" size="sm" @click="onEnqueueTrack(track.id)" v-if="track.id"></q-icon>
              {{ track.title }}<br><router-link
                :to="{ name: 'artist', params: { name: track.artist.name }, query: { mbid: track.artist.mbId, tab: 'overview' } }">{{
                  track.artist.name }}</router-link></td>
            <td>{{ formatSecondsAsTime(Math.round(track.length / 1000)) }}</td>
          </tr>
        </tbody>
      </q-markup-table>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, onMounted, computed, watch, inject, nextTick } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { useQuasar, date } from "quasar";
import { useSpieldoseStore } from "stores/spieldose";
import { api } from 'boot/axios';
import { albumActions, trackActions } from "src/boot/spieldose";


const { t } = useI18n();
const $q = useQuasar();

const route = useRoute();
const router = useRouter();

const spieldoseStore = useSpieldoseStore();

const currentTrackId = computed(() => spieldoseStore.getCurrentPlaylistTrackId );

const currentElementRowIcon = computed(() => {
  if (spieldoseStore.isPlaying) {
    return ('play_arrow');
  } else if (spieldoseStore.isPaused) {
    return ('pause');
  } else if (spieldoseStore.isStopped) {
    return ('stop');
  } else {
    return ('play_arrow');
  }
});

router.beforeEach(async (to, from) => {
  if (to.name == "album") {
    get(route.query.mbId, route.params.title, route.query.artistMBId, route.query.artistName, route.query.year);
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
const totalLength = ref(0);

function get(mbId, title, artistMBId, artistName, year) {
  console.log(1);
  totalTracks.value = 0;
  totalLength.value = 0;
  loading.value = true;
  api.album.get(mbId, title, artistMBId, artistName, year).then((success) => {
    album.value = success.data.album;
    album.value.media.forEach((media) => {
      media.tracks.forEach((track) => {
        totalTracks.value++;
        totalLength.value += track.length;
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
          // TODO: custom message
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
          // TODO: custom message
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

onMounted(() => {
  get(route.query.mbId, route.params.title, route.query.artistMBId, route.query.artistName, route.query.year);
});
</script>
