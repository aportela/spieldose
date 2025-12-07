<template>


  <BrowserBase current-bread-crumb-icon="person" :current-bread-crumb-label="t('Browse artists')" :disable="loading"
    :currentPageIndex="currentPageIndex" :totalPages="totalPages" :totalResults="totalResults"
    @paginationChanged="onPaginationChanged">
    <template #filter>
      <div class="row">
        <q-input class="col-10" v-model.trim="textFilter" dense outlined clearable icon="search"
          label="Search artist name" @update:model-value="skipCount = false" @keydown.enter="browse">
          <template v-slot:prepend>
            <q-icon name="search" />
          </template>
        </q-input>
        <SortFieldSelector class="col-1" :options="sortItems" v-model="currentSortField"
          @update:model-value="onSortFieldChanged" dense outlined label="Sort field" />
        <SortOrderSelector class=" col-1" v-model="currentSortOrder" @update:model-value="onSortOrderChanged" dense
          outlined label="Sort order" />
      </div>
    </template>
    <template #items>
      <ArtistAvatarLink v-for="artist in artists" :key="artist._id" :mbId="artist.mbId" :name="artist.name"
        :image="artist.image" :totalTracks="artist.totalTracks"></ArtistAvatarLink>
    </template>
  </BrowserBase>
</template>

<script setup lang="ts">
import { ref, shallowRef, reactive, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { uid } from "quasar";
import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajax-state";
import { api } from "src/composables/api";
import { default as BrowserBase } from "src/components/BrowserBase.vue";
import { default as ArtistAvatarLink } from "src/components/ArtistAvatarLink.vue"
import { sortOrderSelectorOptions } from "src/types/common";
import {
  type BrowseArtistsResponse as BrowseArtistsResponseInterface,
  type BrowseArtistItemResponse as BrowseArtistItemResponseInterface,
} from "src/types/api-responses";

//import { type Sort as SortInterface, SortClass } from "src/types/sort";

import { default as SortFieldSelector } from "src/components/Forms/Fields/SortFieldSelector.vue";
import { default as SortOrderSelector } from "src/components/Forms/Fields/SortOrderSelector.vue";
const { t } = useI18n();

const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

class Artist implements BrowseArtistItemResponseInterface {
  _id: string;
  name: string;
  mbId: string | null;
  image: string | null;
  totalTracks: number;

  constructor(item: BrowseArtistItemResponseInterface) {
    this._id = uid();
    this.name = item.name;
    this.mbId = item.mbId;
    this.image = item.image;
    this.totalTracks = item.totalTracks;
  }
}

interface SortItem {
  label: string;
  value: string;
};

const sortItems: SortItem[] = [
  {
    label: "Artist name",
    value: "name",
  },
  {
    label: "Artist track count",
    value: "trackCount",
  }
];

const textFilter = ref<string | null>(null);
const currentSortField = ref(sortItems[0]!);
const currentSortOrder = ref(sortOrderSelectorOptions[0]!);

//const sort: SortClass = ref<SortClass>(new SortClass(sortItems[0]!.label, "ASC"));


const currentPageIndex = ref(1);
const totalPages = ref(0);
const totalResults = ref(0);
const warningNoItems = ref(false);
const sortField = ref("name");
const sortOrder = ref("ASC");
const skipCount = ref(false);
const artists = shallowRef<Artist[]>([]);
const loading = ref(false);

function browse() {
  Object.assign(state, defaultAjaxState);
  state.ajaxRunning = true;
  warningNoItems.value = false;
  api.browse.artist({ genre: null, tag: null, name: textFilter.value ?? null }, currentPageIndex.value, 32, sortField.value, sortOrder.value, skipCount.value).then((successResponse: BrowseArtistsResponseInterface) => {
    // create unique id (name can not be used because there are some items with same name but different mbId, like Alice Cooper (artist) && Alice Cooper (band))
    artists.value = successResponse.data.artists.map((item) => {
      return (new Artist(item));
    });
    if (successResponse.data.pager) {
      totalPages.value = successResponse.data.pager.totalPages;
      totalResults.value = successResponse.data.pager.totalResults;
      warningNoItems.value = successResponse.data.pager.totalResults < 1;
      skipCount.value = true; // we receive a new pager vale with total pages/results for current search, if we do not modify the search filters and get another page, total pages/results will be the same, so we can skip count for speeding up calls
    }
    /*
    nextTick(() => {
      if (autoFocusRef.value) {
        autoFocusRef.value.focus();
      }
    });
    */
  }).catch((error: Error) => {
    artists.value = [];
    totalPages.value = 0;
    totalResults.value = 0;
    console.error("Browse artists api error", error);
    // TODO: show error
  }).finally(() => {
    state.ajaxRunning = false;
  });
}

const onPaginationChanged = (pageIndex: number) => {
  currentPageIndex.value = pageIndex;
  browse();
};

const onSortFieldChanged = (value: SortItem) => {
  sortField.value = value.value;
  browse();
};

const onSortOrderChanged = (value: SortItem) => {
  sortOrder.value = value.value;
  browse();
};

onMounted(() => {
  browse();
});
</script>
