<template>
  <q-dialog v-model="visible" @hide="onHide">
    <div v-if="track" style="width: 1024px; max-width: 80vw; background: #fff;">

      <q-card>

        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ t('Track details') }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

      </q-card>
      <q-splitter v-model="splitterModel" unit="px" style="height: 768px" disable after-class="q-pa-none">
        <template v-slot:before>
          <q-img :src="track.covers.normal || 'images/vinyl-medium.png'" @error="track.covers.normal = 'images/vinyl-medium.png'" width="400px" height="400px" spinner-color="pink" />
          <div class="q-pa-md">
            <p class="q-ma-none text-h5 text-grey-9"><q-icon name="music_note" class="q-mr-sm"></q-icon> {{ track.title }}
            </p>
            <p class="q-my-sm text-h5 text-grey-9 q-pl-lg q-ml-md" v-if="track.artist.name">
              {{ t('by') }}
              <a class="text-grey-9" v-if="track.artist.mbId"
                :href="'https://musicbrainz.org/artist/' + track.artist.mbId" target="blank">{{ track.artist.name }}</a>
              <span v-else>{{ track.artist.name }}</span>
            </p>
            <p class="q-ma-none q-pt-md text-h6 text-grey-8" v-if="track.album.title">
              <q-icon name="album" class="q-mr-md"></q-icon>
              <a class="q-ml-xs text-grey-8" v-if="track.album.mbId"
                :href="'https://musicbrainz.org/release/' + track.album.mbId" target="_blank">{{ track.album.title }}</a>
              <span v-else>{{ track.album.title }}</span>
            </p>
            <p class="q-my-none q-pl-md q-ml-lg text-subtitle1 text-grey-8" v-if="track.trackNumber">{{ t('Track number') }}: {{
              track.trackNumber }}</p>
            <p class="q-my-none q-pl-md q-ml-lg text-subtitle1 text-grey-8" v-if="track.album.year">{{ t('On year') }} {{
              track.album.year }}</p>
            <p class="q-mt-md text-subtitle2 text-grey-9" v-if="track.favorited"><q-icon name="favorite"
                class="q-mr-sm"></q-icon> {{ t('Favorited on') }}: {{ date.formatDate(track.favorited * 1000, "YYYY-MM-DD HH:mm:ssZ") }}</p>
          </div>
        </template>
        <template v-slot:after>
          <DashboardBaseBlockChart :globalStats="tab == 'globalStats'" :trackId="trackId">
            <template #prepend>
              <q-btn-group spread color="white" text-color="text-pink-7">
                <q-btn :label="t('My stats')" icon="person" :color="tab == 'myStats' ? 'pink' : 'dark'"
                  @click="onSetTab('myStats')" />
                <q-btn :label="t('Global stats')" icon="public" :color="tab == 'globalStats' ? 'pink' : 'dark'"
                  @click="onSetTab('globalStats')" />
              </q-btn-group>
            </template>
          </DashboardBaseBlockChart>
          <div class="q-pa-md">
            <h4 class="bg-white q-mt-none q-pt-none text-center">{{ t('Lyrics') }}</h4>
            <pre class="q-mt-xl" v-if="track.lyrics">{{ track.lyrics }}</pre>
            <p v-else class="text-h6 text-center text-grey-8"><q-icon name="warning"></q-icon> {{ t('No lyrics found') }}</p>
          </div>
        </template>
      </q-splitter>
    </div>
  </q-dialog>
</template>

<script setup>
import { ref, onMounted } from "vue";
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

const tab = ref('myStats');

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
      caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
    });
    loading.value = false;
  });
}

function onSetTab(t) {
  if (t != tab.value) {
    tab.value = t;
  }
}

onMounted(() => {
  refresh();
});

</script>
