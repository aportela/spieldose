<template>
  <div id="artist-header-block">
    <div id="artist-header-block-background-image" :style="'background-image: url(' + (artistImage || '#') + ')'">
    </div>
    <div id="artist-header-block-background-overlay"></div>
    <div id="artist-header-block-content">
      <div class="q-pl-xl q-pt-xl">
        <p class="text-h3 text-bold">{{ artistName }} <q-icon name="settings" class="rotate" v-if="loading"></q-icon>
        </p>
        <p class="text-white text-bold"><span class="text-grey"><q-icon name="groups" size="sm" class="q-mr-sm"></q-icon>
            Listeners: </span>
          <span class="text-white">0 user/s</span>
        </p>
        <p class="text-white text-bold"><span class="text-grey"><q-icon name="album" size="sm" class="q-mr-sm"></q-icon>
            Total
            plays: </span> <span class="text-white">0 times</span></p>
        <div class="row q-mt-xl">
          <div class="col-6" v-if="artistData.latestAlbum && artistData.latestAlbum.title">
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
              <p class="q-my-none text-white text-weight-bolder header-mini-album-title">{{ artistData.latestAlbum.title
              }}</p>
              <p class="q-mt-none text-white" v-if="artistData.latestAlbum.year">{{ artistData.latestAlbum.year }}</p>
            </div>
          </div>
          <div class="col-6" v-if="artistData.popularAlbum && artistData.popularAlbum.title">
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
              <p class="q-my-none text-white text-weight-bolder header-mini-album-title">{{
                artistData.popularAlbum.title }}</p>
              <p class="q-mt-none text-white" v-if="artistData.popularAlbum.year">{{ artistData.popularAlbum.year }}</p>
            </div>
          </div>
        </div>
      </div>
      <q-tabs class="tex-white q-mt-md">
        <q-route-tab icon="summarize" name="overview" label="Overview"
          :to="{ name: 'artist', params: { name: artistData.name }, query: { mbid: artistData.mbId, tab: 'overview' } }" />
        <q-route-tab icon="menu_book" name="biography" label="Biography"
          :disable="!(artistData.bio && artistData.bio.content)"
          :to="{ name: 'artist', params: { name: artistData.name }, query: { mbid: artistData.mbId, tab: 'biography' } }">
          <q-badge color="pink" floating v-if="artistData.bio && artistData.bio.source == 'wikipedia'">wikipedia</q-badge>
          <q-badge color="pink" floating v-else-if="artistData.bio && artistData.bio.source == 'lastfm'">Last.FM</q-badge>
        </q-route-tab>
        <q-route-tab icon="groups" name="similar" label="Similar artists"
          :to="{ name: 'artist', params: { name: artistData.name }, query: { mbid: artistData.mbId, tab: 'similar' } }">
          <q-badge color="pink" floating v-if="artistData.similar && artistData.similar.length > 0">{{
            artistData.similar.length }}</q-badge>
        </q-route-tab>
        <q-route-tab icon="album" name="albums" label="Albums"
          :to="{ name: 'artist', params: { name: artistData.name }, query: { mbid: artistData.mbId, tab: 'albums' } }">
          <q-badge color="pink" floating v-if="artistData.topAlbums && artistData.topAlbums.length > 0">{{
            artistData.topAlbums.length }}</q-badge>
        </q-route-tab>
        <q-route-tab icon="audiotrack" name="tracks" label="Tracks"
          :to="{ name: 'artist', params: { name: artistData.name }, query: { mbid: artistData.mbId, tab: 'tracks' } }">
          <q-badge color="pink" floating v-if="rows && rows.length > 0">{{ rows.length }}</q-badge>
        </q-route-tab>
        <q-route-tab icon="analytics" name="metrics" label="Stats" disable
          :to="{ name: 'artist', params: { name: artistData.name }, query: { mbid: artistData.mbId, tab: 'metrics' } }" />
      </q-tabs>
    </div>
  </div>
  <q-tab-panels v-model="tab" animated v-if="artistData">
    <q-tab-panel name="overview">
      <div class="row q-col-gutter-lg">
        <div class="col-10">
          <q-card class="my-card shadow-box shadow-10 q-pa-lg" bordered v-if="artistData.bio && artistData.bio.summary">
            <q-card-section>
              <div class="text-h6">Overview</div>
            </q-card-section>
            <q-separator />
            <q-card-section>
              <q-skeleton type="text" square animation="blink" height="300px" v-if="loading" />
              <div v-html="artistData.bio ? artistData.bio.summary || '' : ''">
              </div>
              <q-btn size="sm" @click="tab = 'biography'" v-if="artistData.bio">... read more</q-btn>
              <div v-if="artistData.genres && artistData.genres.length > 0">
                <q-separator class="q-mt-lg"></q-separator>
                <p class="q-mt-md">
                  <ArtistGenreChip v-for="genre in artistData.genres" :key="genre" :name="genre"></ArtistGenreChip>
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
              <q-markup-table v-else>
                <tbody>
                  <tr class="row" v-for="track in artistData.topTracks" :key="track.id">
                    <td class="text-center col-1">
                      <q-icon name="play_arrow" size="sm" :title="t('play track')" class="cursor-pointer q-mr-xs"
                        @click="playTrack(track)" />
                      <q-icon name="add_box" size="sm" :title="t('enqueue track')" class="cursor-pointer q-mr-xs"
                        @click="enqueueTrack(track)" />
                      <q-icon name="favorite" size="md" class="cursor-pointer" :color="track.favorited ? 'pink' : ''"
                        @click="onToggleFavorite(track.id, track.favorited)"></q-icon>
                    </td>
                    <td class="text-bold col-4">{{ track.title }}</td>
                    <td class="text-left col-4">
                      <q-avatar square>
                        <q-img :src="track.covers.small" @error="track.covers.small = 'images/vinyl-small.png'"
                          width="48px" height="48px" spinner-color="pink" />
                      </q-avatar>
                      {{ track.album.title }}
                    </td>
                    <td class="col-2">
                      <q-linear-progress size="32px" :value="track.percentPlay" color="pink-2">
                        <div class="absolute-full flex flex-center">
                          <q-badge class="transparent" text-color="grey-10" :label="track.playCount + ' plays'" />
                        </div>
                      </q-linear-progress>
                    </td>
                  </tr>
                </tbody>
              </q-markup-table>
              <h5 class="text-h5 text-center" v-if="!loading && !(artistData.topTracks.length > 0)"><q-icon name="warning"
                  size="xl"></q-icon> No enought data</h5>
            </q-card-section>
          </q-card>
          <q-card class="my-card shadow-box shadow-10 q-pa-lg q-mt-lg" bordered
            v-if="artistData.topAlbums && artistData.topAlbums.length > 0">
            <q-card-section>
              <div class="text-h6">Top albums</div>
            </q-card-section>
            <q-card-section>
              <q-skeleton type="text" square animation="blink" height="300px" v-if="loading" />
              <div class="q-pa-lg flex flex-center" v-else>
                <div class="q-gutter-md row items-start">
                  <AnimatedAlbumCover v-for="album in artistData.topAlbums.slice(0, 6)" :key="album.title"
                    :image="album.image" :title="album.title" :artistMbId="album.artist.mbId"
                    :artistName="album.artist.name" :year="album.year" @play="onPlayAlbum(album)"
                    @enqueue="onEnqueueAlbum(album)">
                  </AnimatedAlbumCover>
                </div>
              </div>
              <q-btn size="sm" @click="tab = 'albums'" v-if="artistData.topAlbums.length > 6">view more</q-btn>
            </q-card-section>
          </q-card>
          <q-card class="my-card shadow-box shadow-10 q-pa-lg q-mt-lg" bordered
            v-if="artistData.appearsOnAlbums && artistData.appearsOnAlbums.length > 0">
            <q-card-section>
              <div class="text-h6">Appears on</div>
            </q-card-section>
            <q-card-section>
              <q-skeleton type="text" square animation="blink" height="300px" v-if="loading" />
              <div class="q-pa-lg flex flex-center" v-else>
                <div class="q-gutter-md row items-start">
                  <AnimatedAlbumCover v-for="album in artistData.appearsOnAlbums.slice(0, 6)" :key="album.title"
                    :image="album.image" :title="album.title" :artistMbId="album.artist.mbId"
                    :artistName="album.artist.name" :year="album.year" @play="onPlayAlbum(album)"
                    @enqueue="onEnqueueAlbum(album)">
                  </AnimatedAlbumCover>
                </div>
                <q-btn size="sm" @click="tab = 'albums'" v-if="artistData.appearsOnAlbums.length > 6">view more</q-btn>
              </div>
            </q-card-section>
          </q-card>
        </div>
        <div class="col-2">
          <q-card class="my-card shadow-box shadow-10 q-mb-lg" bordered
            v-if="artistData.relations && artistData.relations.length > 0">
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
          <q-card class="my-card shadow-box shadow-10 q-mb-lg" bordered
            v-if="artistData.similar && artistData.similar.length > 0">
            <q-card-section>
              Similar
            </q-card-section>
            <q-separator />
            <q-card-section>
              <div class="row">
                <div class="col-4" v-for="similar in artistData.similar.slice(0, 3)" :key="similar.name">
                  <p class="text-center">
                    <router-link
                      :to="{ name: 'artist', params: { name: similar.name }, query: { mbid: similar.mbId, tab: 'overview' } }"
                      style="text-decoration: none">
                      <q-img class="q-mr-sm q-mb-sm rounded-borders" style="border-radius: 50%" :src="similar.image"
                        fit="cover" width="96px" height="96px" spinner-color="pink" />
                      <br>{{ similar.name
                      }}</router-link>
                  </p>
                </div>
                <q-btn size="sm" @click="tab = 'similarArtists'">view more</q-btn>
              </div>
            </q-card-section>
          </q-card>
          <q-card class="my-card shadow-box shadow-10" bordered>
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
            <q-card-section id="artist_bio_content">
              <WikipediaPage :htmlPage="artistData.bio ? artistData.bio.content || '' : ''"></WikipediaPage>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-tab-panel>
    <q-tab-panel name="similar">
      <q-card class="my-card shadow-box shadow-10 q-pa-lg" bordered style="min-height: 344px;">
        <q-card-section>
          <div class="text-h6">Similar artists</div>
        </q-card-section>
        <q-separator />
        <q-card-section v-if="artistData.similar && artistData.similar.length > 0">
          <div class="q-gutter-md row items-start">
            <router-link
              :to="{ name: 'artist', params: { name: artist.name }, query: { mbid: artist.mbId, tab: 'overview' } }"
              v-for="artist in artistData.similar" :key="artist.name">
              <q-img img-class="artist_image" :src="artist.image || '#'" width="250px" height="250px" fit="cover">
                <div class="absolute-bottom text-subtitle1 text-center">
                  {{ artist.name }}
                  <p class="text-caption q-mb-none">{{ artist.totalTracks + " " + (artist.totalTracks > 1 ? 'tracks' :
                    'track') }}</p>
                </div>
                <template v-slot:loading>
                  <div class="absolute-full flex flex-center bg-grey-3 text-dark">
                    <q-spinner color="pink" size="xl" />
                    <div class="absolute-bottom text-subtitle1 text-center bg-grey-5 q-py-md">
                      {{ artist.name }}
                      <p class="text-caption q-mb-none">{{ artist.totalTracks + " " + (artist.totalTracks > 1 ? 'tracks' :
                        'track') }}</p>
                    </div>
                  </div>
                </template>
                <template v-slot:error>
                  <div class="absolute-full flex flex-center bg-grey-3 text-dark">
                    <div class="absolute-bottom text-subtitle1 text-center bg-grey-5 q-py-md">
                      {{ artist.name }}
                      <p class="text-caption q-mb-none">{{ artist.totalTracks + " " + (artist.totalTracks > 1 ? 'tracks' :
                        'track') }}</p>
                    </div>
                  </div>
                </template>
              </q-img>
            </router-link>
          </div>
        </q-card-section>
        <q-card-section v-else>
          No similar artists found
        </q-card-section>

      </q-card>

    </q-tab-panel>
    <q-tab-panel name="albums">
      <q-card class="my-card shadow-box shadow-10 q-pa-lg" bordered style="min-height: 344px;">
        <q-card-section>
          <div class="text-h6">Albums</div>
        </q-card-section>
        <q-separator />
        <q-card-section v-if="artistData.topAlbums && artistData.topAlbums.length > 0">
          <div class="q-gutter-md row items-start q-mt-sm">
            <AnimatedAlbumCover v-for="album in artistData.topAlbums" :key="album.title" :image="album.image"
              :title="album.title" :artistMbId="album.artist.mbId" :artistName="album.artist.name" :year="album.year"
              @play="onPlayAlbum(album)" @enqueue="onEnqueueAlbum(album)">
            </AnimatedAlbumCover>
          </div>
        </q-card-section>
      </q-card>
    </q-tab-panel>
    <q-tab-panel name="tracks">
      <q-card class="my-card shadow-box shadow-10 q-pa-lg" bordered style="min-height: 344px;">
        <q-card-section>
          <div class="text-h6">Tracks
            <q-btn-group class="q-ml-md">
              <q-btn size="sm" icon="play_arrow" @click.prevent="onPlayAllArtistTracks">play all</q-btn>
              <q-btn size="sm" icon="add_box" @click.prevent="onEnqueueAllArtistTracks">enqueue all</q-btn>
            </q-btn-group>
          </div>
        </q-card-section>
        <q-separator />
        <q-card-section>
          <q-table ref="tableRef" class="my-sticky-header-table" style="height: 46.8em" :rows="rows"
            :columns="artistTracksColumns" row-key="id" virtual-scroll :rows-per-page-options="[0]" :hide-bottom="true">
            <template v-slot:body="props">
              <q-tr class="cursor-pointer" :props="props"
                @click="(evt) => onRowClick(evt, props.row, props.row.index - 1)">
                <q-td key="index" :props="props">
                  <q-icon :name="rowIcon" color="pink" size="sm" class="q-mr-sm" v-if="false"></q-icon>
                  {{ props.row.index }} / {{ rows.length }}
                </q-td>
                <q-td key="title" :props="props">
                  {{ props.row.title }}
                </q-td>
                <q-td key="artist" :props="props">
                  <router-link v-if="props.row.artist.name" :class="{ 'text-white text-bold': false }"
                    :to="{ name: 'artist', params: { name: props.row.artist.name }, query: { mbid: props.row.artist.mbId, tab: 'overview' } }"><q-icon
                      name="link" class="q-mr-sm"></q-icon>{{
                        props.row.artist.name }}</router-link>
                </q-td>
                <q-td key="albumArtist" :props="props">
                  <router-link v-if="props.row.album.artist.name" :class="{ 'text-white text-bold': false }"
                    :to="{ name: 'artist', params: { name: props.row.album.artist.name }, query: { mbid: props.row.album.artist.mbId, tab: 'overview' } }"><q-icon
                      name="link" class="q-mr-sm"></q-icon>{{ props.row.album.artist.name }}</router-link>
                </q-td>
                <q-td key="albumTitle" :props="props">
                  {{ props.row.album.title }}
                </q-td>
                <q-td key="albumTrackIndex" :props="props">
                  {{ props.row.trackNumber }}
                </q-td>
                <q-td key="year" :props="props">
                  {{ props.row.album.year }}
                </q-td>
                <q-td key="actions" :props="props">
                  <q-btn-group outline>
                    <q-btn size="sm" color="white" text-color="grey-5" icon="play_arrow" :title="t('Play')"
                      @click="trackActions.play(props.row)" />
                    <q-btn size="sm" color="white" :text-color="props.row.favorited ? 'pink' : 'grey-5'" icon="favorite"
                      :title="t('Toggle favorite')" @click="onToggleFavorite(props.row.id, props.row.favorited)" />
                  </q-btn-group>
                </q-td>
              </q-tr>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </q-tab-panel>
    <q-tab-panel name="metrics">
      <q-card class="my-card shadow-box shadow-10 q-pa-lg" bordered style="min-height: 344px;">
        <q-card-section>
          <div class="text-h6">Stats</div>
        </q-card-section>
        <q-separator />
        <q-card-section>
          Lorem ipsum dolor sit amet consectetur adipisicing elit.
        </q-card-section>
      </q-card>
    </q-tab-panel>
  </q-tab-panels>
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
  height: 416px;
  position: relative;
}

div#artist-header-block-background-image {
  background-size: cover;
  background-repeat: no-repeat;
  background-position: 50% 25%;
  width: 50%;
  height: 416px;
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
  height: 416px;
  position: absolute;
  top: 0;
  right: 0;
  z-index: 1;
}

div#artist-header-block-content {
  width: 51%;
  z-index: 2;
}

p.header-mini-album-title {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  width: 16em;
}

img.artist_image {
  -webkit-filter: grayscale(100%) blur(4px) opacity(0.5);
  /* Safari 6.0 - 9.0 */
  filter: grayscale(100%) blur(4px) opacity(0.5);
  transition: filter 0.2s ease-in;
}

img.artist_image:hover {
  -webkit-filter: none;
  /* Safari 6.0 - 9.0 */
  filter: none;
  transition: filter 0.2s ease-out;

}
</style>

<style lang="scss">
@import "~chartist/dist/index.css";
</style>

<script setup>

import { ref, onMounted, computed, watch, inject } from "vue";
import { useRoute } from "vue-router";
import { useI18n } from "vue-i18n";
import { useQuasar, date } from "quasar";
import { api } from 'boot/axios';
import { LineChart, FixedScaleAxis } from 'chartist';

import { default as ArtistURLRelationshipChip } from 'components/ArtistURLRelationshipChip.vue';
import { default as ArtistGenreChip } from 'components/ArtistGenreChip.vue';
import { default as AnimatedAlbumCover } from "components/AnimatedAlbumCover.vue";
import { default as WikipediaPage } from "components/WikipediaPage.vue";

import { trackActions, albumActions } from "boot/spieldose";

import { spieldoseEventNames } from "boot/events";
import { useSpieldoseStore } from "stores/spieldose";

const bus = inject('bus');

bus.on(spieldoseEventNames.track.setFavorite, (data) => {
  if (artistData.value && artistData.value.topTracks) {
    const index = artistData.value.topTracks.findIndex(
      (element) => element && element.id == data.id
    );
    if (index !== -1) {
      artistData.value.topTracks[index].favorited = data.timestamp;
    }
  }
  if (rows.value && rows.value.length > 0) {
    const index = rows.value.findIndex(
      (element) => element && element.id == data.id
    );
    if (index !== -1) {
      rows.value[index].favorited = data.timestamp;
    }
  }
});

bus.on(spieldoseEventNames.track.unSetFavorite, (data) => {
  if (artistData.value && artistData.value.topTracks) {
    const index = artistData.value.topTracks.findIndex(
      (element) => element && element.id == data.id
    );
    if (index !== -1) {
      artistData.value.topTracks[index].favorited = null;
    }
  }
  if (rows.value && rows.value.length > 0) {
    const index = rows.value.findIndex(
      (element) => element && element.id == data.id
    );
    if (index !== -1) {
      rows.value[index].favorited = null;
    }
  }
});

const { t } = useI18n();
const $q = useQuasar();

const spieldoseStore = useSpieldoseStore();

const route = useRoute();

const tabFromRoute = computed(() => route.query.tab || 'overview');

watch(tabFromRoute, (newValue) => {
  if (newValue && ['overview', 'biography', 'similar', 'albums', 'tracks', 'metrics'].includes(newValue)) {
    tab.value = newValue;
  } else {
    tab.value = 'overview';
  }
});

const artistMBId = ref(route.query.mbid);
const artistName = ref(route.params.name);

const tab = ref(route.query.tab && ['overview', 'biography', 'similar', 'albums', 'tracks', 'metrics'].includes(route.query.tab) ? route.query.tab : 'overview');

watch(tab, (newValue) => {
  if (newValue) {
    window.scrollTo(0, 0);
  }
});

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
  topAlbums: [],
  topTracks: [],
  similar: [],
  tracks: []
});

const artistImage = ref(null);

const currentArtist = computed(() => { return (route.query.name); });

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
    tab.value = 'overview';
    get(artistMBId.value, artistName.value);
  }
});


const rows = ref([]);
const artistTracksColumns = [
  {
    name: 'index',
    required: true,
    label: 'Index',
    align: 'right',
    field: row => row.index,
    sortable: true
  },
  {
    name: 'title',
    required: true,
    label: 'Title',
    align: 'left',
    field: row => row.title,
    sortable: true
  },
  {
    name: 'artist',
    required: true,
    label: 'Artist',
    align: 'left',
    field: row => row.artist.name,
    sortable: true
  },
  {
    name: 'albumArtist',
    required: false,
    label: 'Album artist',
    align: 'left',
    field: row => row.album.artist.name,
    sortable: true
  },
  {
    name: 'albumTitle',
    required: false,
    label: 'Album',
    align: 'left',
    field: row => row.album.title,
    sortable: true
  },
  {
    name: 'albumTrackIndex',
    required: false,
    label: 'Album Track nº',
    align: 'right',
    field: row => row.trackNumber,
    sortable: true
  },
  {
    name: 'year',
    required: false,
    label: 'Year',
    align: 'right',
    field: row => row.album.year,
    sortable: true
  },
  {
    name: 'actions',
    required: true,
    label: 'Actions',
    align: 'center',
    favorited: row => row.favorited
  },
];

onMounted(() => {
  /*
  const chartOptions = {
    low: 0,
    showArea: true,
    fullWidth: true,
    chartPadding: { left: 48, right: 48 },
    axisX: {
      type: FixedScaleAxis,
      divisor: 5,
      labelInterpolationFnc: function(value) {
        console.log(value);
        return date.formatDate(value, 'MMM');
      }
    },
    axisY: {
    }
  };

  let count = 16;

  let labels = [];
  let series = [];
  while (count > 0) {
    labels.push(date.formatDate(date.addToDate(new Date(), { months: -count }), 'X'));
    series.push(Math.round(Math.random() * 501))
    count--;
  }

  const data = {
    labels : labels,
    series: [series]
  };

  new LineChart('.ct-chart', data, chartOptions);
  */
});

// https://gist.github.com/yidas/41cc9272d3dff50f3c9560fb05e7255e
function nl2br(str, replaceMode, isXhtml) {
  var breakTag = (isXhtml) ? '<br />' : '<br>';
  var replaceStr = (replaceMode) ? '$1' + breakTag : '$1' + breakTag + '$2';
  return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, replaceStr);
}

function playTrack(track) {
  trackActions.play([{ track: track }]);
}

function enqueueTrack(track) {
  trackActions.enqueue([{ track: track }]);
}

function get(mbId, name) {
  loading.value = true;
  api.artist.get(mbId, name).then((success) => {
    try {
      artistData.value = success.data.artist;
      artistImage.value = artistData.value.image;
      let totalPlays = 0;
      artistData.value.topTracks.forEach((track) => {
        totalPlays += track.playCount;
      });
      artistData.value.topTracks = artistData.value.topTracks.map((track) => {
        if (track.coverPathId) {
          track.image = "api/2/thumbnail/small/local/album/?path=" + encodeURIComponent(track.coverPathId);
        } else if (track.covertArtArchiveURL) {
          track.image = "api/2/thumbnail/small/remote/album/?url=" + encodeURIComponent(track.covertArtArchiveURL);
        } else {
          track.image = null;
        }
        track.percentPlay = Math.round(track.playCount * 100 / totalPlays) / 100;
        return (track);
      });

      artistData.value.topAlbums.value = artistData.value.topAlbums.map((item) => {
        item.image = item.covers.small;
        return (item);
      });
      artistData.value.appearsOnAlbums.value = artistData.value.appearsOnAlbums.map((item) => {
        item.image = item.covers.small;
        return (item);
      });
      rows.value = success.data.artist.tracks.map((element, index) => { element.index = index + 1; return (element) });
    } catch (e) { console.log(e); }
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

function onPlayAlbum(album) {
  albumActions.play(album).then((success) => {
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

function onEnqueueAlbum(album) {
  albumActions.enqueue(album).then((success) => {
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

function onRowClick(evt, row, index) {
  if (evt.target.nodeName != 'A' && evt.target.nodeName != 'I' && evt.target.nodeName != 'BUTTON') { // PREVENT play if we are clicking on action buttons
    spieldoseStore.interact();
  }
}

function onToggleFavorite(trackId, favorited) {
  const funct = !favorited ? trackActions.setFavorite : trackActions.unSetFavorite;
  funct(trackId).then((success) => {
  })
    .catch((error) => {
      switch (error.response.status) {
        default:
          // TODO: custom message
          $q.notify({
            type: "negative",
            message: t("API Error: error when toggling favorite flag"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
    });
}

function onPlayAllArtistTracks() {
  spieldoseStore.sendElementsToCurrentPlaylist(artistData.value.tracks.map((element) => { return ({ track: element }); }));
}

function onEnqueueAllArtistTracks() {
  spieldoseStore.appendElementsToCurrentPlaylist(artistData.value.tracks.map((element) => { return ({ track: element }); }));
}

get(artistMBId.value, artistName.value);

</script>
