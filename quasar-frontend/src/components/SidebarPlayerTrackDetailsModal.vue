<template>
  <q-dialog v-model="visible" @hide="onHide">
    <q-card class="my-card" v-if="track">
      <q-img :src="track.covers.normal" width="400px" height="400px" spinner-color="pink" />
      <q-card-section>
        <div>
            <p>Title: {{ track.title  }}</p>
            <p>Artist: {{ track.artist.name }}<br><q-btn size="sm" outline dense v-if="track.artist.mbId" icon="link" label="musicbrainz" :href="'https://musicbrainz.org/artist/' + track.artist.mbId" target="blank"></q-btn></p>
            <p>Album: {{ track.album.title }}<br><q-btn size="sm" outline dense v-if="track.album.mbId" icon="link" label="musicbrainz" :href="'https://musicbrainz.org/release/' + track.album.mbId" target="blank"></q-btn></p>
            <p>Album track index: {{ track.trackNumber }}</p>
            <p>Year: {{ track.album.year }}</p>
            <p v-if="track.favorited">Favorited at: {{ date.formatDate(track.favorited * 1000, "YYYY-MM-DD HH:mm:ss Z") }}</p>
        </div>
      </q-card-section>
      <q-separator />
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from "vue";
import { useQuasar, date } from "quasar";
import { useI18n } from 'vue-i18n';
import { api } from "src/boot/axios";

const $q = useQuasar();
const { t } = useI18n();

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
    console.log(success);
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
