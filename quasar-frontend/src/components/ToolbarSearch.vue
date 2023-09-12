<template>
  <q-select ref="search" dense standout use-input hide-selected class="q-mx-md" filled color="pink" :stack-label="false"
    :label="t('Search...')" v-model="searchText" :options="filteredOptions" @filter="onFilter" style="min-width: 24%;" >
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
        <q-item>
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
import { trackActions } from '../boot/spieldose';

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
        api.track.search({ title: val }, 1, 5, false, 'title', 'ASC')
          .then((success) => {
            searchResults.value = success.data.data.items;
            filteredOptions.value = searchResults.value.map((item) => {
              return ({ id: item.id, label: item.title, caption: t('fastSearchResultCaption', { artistName: item.artist.name, albumTitle: item.album.title, albumYear: item.album.year }) });
            });
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
  const element = searchResults.value.find((element) => element.id == trackId);
  if (element) {
    trackActions.play(element);
  }
}

function onAppendTrack(trackId) {
  const element = searchResults.value.find((element) => element.id == trackId);
  if (element) {
    trackActions.enqueue(element);
  }
}

</script>
