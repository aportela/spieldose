<template>
  <q-dialog v-model="visible" @hide="onHide">
    <div v-if="track" style="width: 1024px; max-width: 80vw; background: #fff;">
      <q-splitter v-model="splitterModel" unit="px" style="height: 768px" disable after-class="q-pa-none">
        <template v-slot:before>
            <q-img :src="track.covers.normal" width="400px" height="400px" spinner-color="pink" />
            <div class="q-pa-md">
            <p class="q-ma-none text-h5 text-grey-9"><q-icon name="music_note" class="q-mr-sm"></q-icon> {{ track.title }}</p>
            <p class="q-my-sm text-h5 text-grey-9 q-pl-lg q-ml-md" v-if="track.artist.name">
              by
              <a class="text-grey-9" v-if="track.artist.mbId" :href="'https://musicbrainz.org/artist/' + track.artist.mbId"
                target="blank">{{ track.artist.name }}</a>
              <span v-else>{{ track.artist.name }}</span>
            </p>
            <p class="q-ma-none q-pt-md text-h6 text-grey-8" v-if="track.album.title">
              <q-icon name="album" class="q-mr-md"></q-icon>
              <a class="q-ml-xs text-grey-8" v-if="track.album.mbId" :href="'https://musicbrainz.org/release/' + track.album.mbId" target="_blank">{{ track.album.title}}</a>
              <span v-else>{{ track.album.title}}</span>
            </p>
            <p class="q-my-none q-pl-md q-ml-lg text-subtitle1 text-grey-8" v-if="track.trackNumber">track number: {{ track.trackNumber }}</p>
            <p class="q-my-none q-pl-md q-ml-lg text-subtitle1 text-grey-8" v-if="track.album.year">on year {{ track.album.year }}</p>
            <p class="q-mt-md text-subtitle2 text-grey-9" v-if="track.favorited"><q-icon name="favorite" class="q-mr-sm"></q-icon> Favorited at: {{ date.formatDate(track.favorited * 1000, "YYYY-MM-DD HH:mm:ss Z") }}</p>
          </div>
        </template>
        <template v-slot:after>
          <DashboardBaseBlockChart :globalStats="tab == 'globalStats'" :trackId="trackId"></DashboardBaseBlockChart>
          <div class="q-pa-md">
            <h4 class="bg-white q-mt-none q-pt-none text-center">Lyrics</h4>
            <pre class="q-mt-xl" v-if="track.lyrics">{{ track.lyrics }}</pre>
            <p v-else class="text-h6 text-center text-grey-8"><q-icon name="warning"></q-icon> No lyrics found</p>
          </div>
        </template>
      </q-splitter>
    </div>
  </q-dialog>
</template>


<script setup>
import { ref } from "vue";
import { useQuasar, date } from "quasar";
import { useI18n } from 'vue-i18n';
import { api } from "src/boot/axios";
import { default as DashboardBaseBlockChart } from 'components/DashboardBaseBlockChart.vue';

const $q = useQuasar();
const { t } = useI18n();

const splitterModel = ref(400);
const props = defineProps({
  trackId: String,
  coverImage: String
});

const loading = ref(false);
const emit = defineEmits(['hide']);

const visible = ref(true);

function onHide() {
  emit('hide');
}

const track = ref(null);

function refresh() {
  if (props.trackId)
    loading.value = true;
  api.track.get(props.trackId).then((success) => {
    loading.value = false;
    track.value = success.data.track;
  }).catch((error) => {
    loading.value = false;
    // TODO: custom message & translations
    $q.notify({
      type: "negative",
      message: "API Error: error loading track details",
      caption: "API Error: fatal error details: HTTP {" + error.response.status + "} ({" + error.response.statusText + "})"
    });
    loading.value = false;
  });


}

refresh();
</script>
