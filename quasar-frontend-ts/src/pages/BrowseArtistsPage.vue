<template>
  <BrowserBase current-bread-crumb-icon="person" :current-bread-crumb-label="t('Browse artists')" :disable="loading"
    :currentPageIndex="currentPageIndex" :totalPages="totalPages" :totalResults="totalResults"
    @paginationChanged="onPaginationChanged">
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
import { type BrowseArtistsResponse as BrowseArtistsResponseInterface, BrowseArtistItemResponse as BrowseArtistItemResponseInterface } from "src/types/api-responses";

const { t } = useI18n();

const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

interface Artist extends BrowseArtistItemResponseInterface {
  _id: string;
};

const currentPageIndex = ref(1);
const totalPages = ref(0);
const totalResults = ref(0);
const warningNoItems = ref(false);
const sortField = ref(null);
const sortOrder = ref(null);
const skipCount = ref(false);
const artists = shallowRef([]);
const loading = ref(false);

function browse() {
  Object.assign(state, defaultAjaxState);
  state.ajaxRunning = true;
  warningNoItems.value = false;
  api.browse.artist({ genre: null, tag: null, name: null }, currentPageIndex.value, 32, sortField.value, sortOrder.value, skipCount.value).then((successResponse: BrowseArtistsResponseInterface) => {
    // create unique id (name can not be used because there are some items with same name but different mbId, like Alice Cooper (artist) && Alice Cooper (band))
    artists.value = successResponse.data.data.items.map((item) => {
      return ({
        _id: uid(),
        name: item.name,
        mbId: item.mbId ?? undefined,
        image: item.image ?? undefined,
        totalTracks: item.totalTracks
      });
    });
    if (successResponse.data.data.pager) {
      totalPages.value = successResponse.data.data.pager.totalPages;
      totalResults.value = successResponse.data.data.pager.totalResults;
      warningNoItems.value = successResponse.data.data.pager.totalResults < 1;
      skipCount.value = true;
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

onMounted(() => {
  browse();
});
</script>