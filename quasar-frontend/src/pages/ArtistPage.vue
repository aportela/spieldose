<template>
  <q-page>
    <div id="artist-header-block">
      <div id="artist-header-block-background-image" :style="'background-image: url(' + (artistImage || '#') + ')'">
      </div>
      <div id="artist-header-block-background-overlay"></div>
      <div id="artist-header-block-content">
        <div class="q-pl-xl q-pt-xl">
          <p class="text-h3 text-bold">{{ artistName }} <q-icon name="settings" class="rotate" v-if="loading"></q-icon>
          </p>
          <p class="text-white text-bold"><span class="text-grey"><q-icon name="groups" size="sm"
                class="q-mr-sm"></q-icon> Listeners: </span>
            <span class="text-white">0 user/s</span>
          </p>
          <p class="text-white text-bold"><span class="text-grey"><q-icon name="album" size="sm" class="q-mr-sm"></q-icon>
              Total
              plays: </span> <span class="text-white">0 times</span></p>
          <div class="row q-mt-xl">
            <div class="col-6" v-if="artistData.latestAlbum.title">
              <div class="float-left" style="width: 96px; height:96px;">
                <q-img :src="artistData.latestAlbum.image" spinner-color="white" style="height: 96px; max-width: 96px">
                  <template v-slot:error>
                    <div class="absolute-full flex flex-center bg-grey-0">
                    </div>
                  </template>
                </q-img>
              </div>
              <div class="float-left oneline-ellipsis" style="margin-left: 24px; margin-top: 10px;">
                <p class="q-mb-none text-grey">LATEST RELEASE</p>
                <p class="q-my-none text-white text-weight-bolder header-mini-album-title">{{ artistData.latestAlbum.title }}</p>
                <p class="q-mt-none text-white" v-if="artistData.latestAlbum.year">{{ artistData.latestAlbum.year }}</p>
              </div>
            </div>
            <div class="col-6" v-if="artistData.popularAlbum.title">
              <div class="float-left oneline-ellipsis" style="width: 96px; height:96px;">
                <q-img :src="artistData.popularAlbum.image" spinner-color="white" style="height: 96px; max-width: 96px">
                  <template v-slot:error>
                    <div class="absolute-full flex flex-center bg-grey-0">
                    </div>
                  </template>
                </q-img>
              </div>
              <div class="float-left oneline-ellipsis" style="margin-left: 24px; margin-top: 10px;">
                <p class="q-mb-none text-grey">POPULAR</p>
                <p class="q-my-none text-white text-weight-bolder header-mini-album-title">{{ artistData.popularAlbum.title }}</p>
                <p class="q-mt-none text-white" v-if="artistData.popularAlbum.year">{{ artistData.popularAlbum.year }}</p>
              </div>
            </div>
          </div>
        </div>
        <q-tabs v-model="tab" class="tex-white q-mt-md">
          <q-tab name="overview" label="Overview" />
          <q-tab name="biography" label="Biography" />
          <q-tab name="similarArtists" label="Similar artists" />
          <q-tab name="albums" label="Albums" />
          <q-tab name="tracks" label="Tracks" />
          <q-tab name="stats" label="Stats" />
        </q-tabs>
      </div>
    </div>
    <q-tab-panels v-model="tab" animated v-if="artistData">
      <q-tab-panel name="overview">
        <div class="row q-col-gutter-lg">
          <div class="col-10">
            <q-card class="my-card shadow-box shadow-10 q-pa-lg" bordered>
              <q-card-section>
                <div class="text-h6">Overview</div>
              </q-card-section>
              <q-separator />
              <q-card-section>
                <q-skeleton type="text" square animation="blink" height="300px" v-if="loading" />
                <div v-else>
                  <div v-html="artistData.bio ? nl2br(artistData.bio.summary) : null">
                  </div>
                  <p class="q-mt-md" v-if="artistData.relations">
                    <ArtistURLRelationshipChip v-for="relation in artistData.relations" :key="relation.url"
                      :id="relation['type-id']" :url="relation.url"></ArtistURLRelationshipChip>
                  </p>
                </div>
              </q-card-section>
            </q-card>
            <q-card class="my-card shadow-box shadow-10 q-pa-lg q-mt-lg" bordered>
              <q-card-section>
                <div class="text-h6">Top tracks</div>
              </q-card-section>
              <q-separator />
              <q-card-section>
                <q-skeleton type="text" square animation="blink" height="300px" v-if="loading" />
                <q-markup-table v-else dense>
                  <tbody>
                    <tr v-for="track, index in artistData.topTracks" :key="track.id">
                      <td class="text-right">{{ index }}</td>
                      <td class="text-center"><q-icon name="play_arrow" size="lg" class="cursor-pointer"></q-icon></td>
                      <td class="text-left">
                        <q-avatar>
                          <img :src="track.image">
                        </q-avatar>
                        {{ track.album }}
                      </td>
                      <td class="text-center"><q-icon name="favorite_border" size="lg" class="cursor-pointer"></q-icon>
                      </td>
                      <td class="text-bold">{{ track.title }}</td>
                      <td>
                        <p :style="'height: 100%; width: ' + (22 * (artistData.topTracks.length - index)) + '%'"
                          class="bg-pink text-white">{{ 22 * (artistData.topTracks.length - index) }} plays</p>
                      </td>
                    </tr>
                  </tbody>
                </q-markup-table>
              </q-card-section>
            </q-card>
            <q-card class="my-card shadow-box shadow-10 q-pa-lg q-mt-lg" bordered v-if="artistData.topAlbums">
              <q-card-section>
                <div class="text-h6">Top albums</div>
              </q-card-section>
              <q-card-section>
                <q-skeleton type="text" square animation="blink" height="300px" v-if="loading" />
                <div class="q-pa-lg flex flex-center" v-else>
                  <div class="q-gutter-md row items-start">
                    <AnimatedAlbumCover v-for="album in artistData.topAlbums" :key="album.title" :album="album">
                    </AnimatedAlbumCover>
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-2">
            <q-card class="my-card shadow-box shadow-10" bordered v-if="artistData.relations">
              <q-card-section>
                Relations
              </q-card-section>
              <q-separator />
              <q-card-section>
                <q-skeleton type="text" square animation="blink" height="300px" v-if="loading" />
                <p v-for="relation in artistData.relations" :key="relation.url">
                  <ArtistURLRelationshipChip :id="relation['type-id']" :url="relation.url"></ArtistURLRelationshipChip>
                </p>
              </q-card-section>
            </q-card>
            <q-card class="my-card shadow-box shadow-10 q-mt-lg" bordered>
              <q-card-section>
                Similar
              </q-card-section>
              <q-separator />
              <q-card-section>
                <div class="row">
                  <div class="col-4" v-for="similar in artistData.similar" :key="similar.name">
                    <p class="text-center">
                      <router-link :to="{ name: 'artist', params: { name: similar.name } }" style="text-decoration: none">
                      <q-img class="q-mr-sm q-mb-sm rounded-borders" style="border-radius: 50%" :src="similar.image"
                        fit="cover" width="96px" height="96px" spinner-color="pink" />
                      <br>{{ similar.name
                      }}</router-link>
                    </p>
                  </div>
                </div>
              </q-card-section>
            </q-card>
            <q-card class="my-card shadow-box shadow-10 q-mt-lg" bordered>
              <q-card-section>
                Stats
              </q-card-section>
              <q-separator />
              <q-card-section>
                <div class="ct-chart"></div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-tab-panel>

      <q-tab-panel name="biography">
        <div class="row q-col-gutter-lg">
          <div class="col-12">
            <q-card class="my-card shadow-box shadow-10 q-pa-lg" bordered>
              <q-card-section>
                <div class="text-h6">Biography</div>
              </q-card-section>
              <q-separator />
              <q-card-section>
                <div v-html="artistData.bio ? nl2br(artistData.bio.content) : null"></div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-tab-panel>

      <q-tab-panel name="similarArtists">
        <div class="text-h6">Similar artists</div>
        Lorem ipsum dolor sit amet consectetur adipisicing elit.
      </q-tab-panel>
      <q-tab-panel name="albums">
        <div class="text-h6">Albums</div>
        Lorem ipsum dolor sit amet consectetur adipisicing elit.
      </q-tab-panel>
      <q-tab-panel name="tracks">
        <div class="text-h6">Tracks</div>
        Lorem ipsum dolor sit amet consectetur adipisicing elit.
      </q-tab-panel>
      <q-tab-panel name="stats">
        <div class="text-h6">Stats</div>
        Lorem ipsum dolor sit amet consectetur adipisicing elit.
      </q-tab-panel>
    </q-tab-panels>
  </q-page>
</template>

<style>

.rotate {
  width: 100px;
  animation: rotation 2s infinite linear;
}

@keyframes rotation {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(359deg);
  }
}

div#artist-header-block {
  overflow: hidden;
  color: #fff;
  background: rgba(24, 24, 24, 1);
  height: 400px;
  position: relative;
}

div#artist-header-block-background-image {
  background-size: cover;
  background-repeat: no-repeat;
  background-position: 50% 25%;
  width: 50%;
  height: 400px;
  position: absolute;
  top: 0;
  right: 0;
  z-index: 0;
  filter: blur(2px);
  transition: filter 0.3s ease-in;
}

div#artist-header-block-background-overlay {
  background: linear-gradient(0.25turn, rgba(24, 24, 24, 1), rgba(92, 71, 59, 0));
  width: 51%;
  height: 400px;
  position: absolute;
  top: 0;
  right: 0;
  z-index: 1;
}

div#artist-header-block-content {
  width: 50%;
  z-index: 2;
}

p.header-mini-album-title {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  width: 16em;
}
</style>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useQuasar } from "quasar";
import { api } from 'boot/axios'
import { BarChart } from 'chartist';
import { default as ArtistURLRelationshipChip } from 'components/ArtistURLRelationshipChip.vue';
import { default as AnimatedAlbumCover } from "components/AnimatedAlbumCover.vue";

const { t } = useI18n();
const $q = useQuasar();

const route = useRoute()
const artistName = ref(route.params.name);

const tab = ref('overview');

const loading = ref(false);

const artistData = ref({
  mbId: null,
  name: null,
  popularAlbum: {
    title: null,
    year: null,
    image: null
  },
  latestAlbum: {
    title: null,
    year: null,
    image: null
  },
  topTracks: [],
  similar: []
});

const artistImage = ref(null);

const currentArtist = computed(() => { return (route.params.name); });

watch(currentArtist, (newValue, oldValue) => {

  artistName.value = newValue;
  if (artistName.value) {
    artistData.value = {
      mbId: null,
      name: null,
      popularAlbum: {
        title: null,
        year: null,
        image: null
      },
      latestAlbum: {
        title: null,
        year: null,
        image: null
      },
      topTracks: [],
      similar: []
    }
    artistImage.value = null;
    get(artistName.value);
  }
});

onMounted(() => {
  /*
    new BarChart('.ct-chart', {
      labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
      series: [
        [12, 9, 7, 8, 5, 4, 3]
      ]
    }, {
      fullWidth: true,
      chartPadding: {
        right: 40
      }
    });
*/
});

// https://gist.github.com/yidas/41cc9272d3dff50f3c9560fb05e7255e
function nl2br(str, replaceMode, isXhtml) {
  var breakTag = (isXhtml) ? '<br />' : '<br>';
  var replaceStr = (replaceMode) ? '$1' + breakTag : '$1' + breakTag + '$2';
  return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, replaceStr);
}


function get(name) {
  loading.value = true;
  api.artist.get(name)
    .then((success) => {
      artistData.value = success.data.artist;
      artistImage.value = artistData.value.image;
      loading.value = false;
    })
    .catch((error) => {
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

get(artistName.value);
</script>
