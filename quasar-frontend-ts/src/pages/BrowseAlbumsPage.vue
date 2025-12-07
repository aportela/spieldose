<template>
  <BrowserBase current-bread-crumb-icon="album" :current-bread-crumb-label="t('Browse albums')" :disable="loading"
    :currentPageIndex="currentPageIndex" :totalPages="totalPages" :totalResults="totalResults"
    @paginationChanged="onPaginationChanged">
    <template #items>
      <AnimatedAlbumCover v-for="album in albums" :key="album._id" :image="album.image" :albumTitle="album.title"
        :albumMbId="album.mbId" :artistMbId="album.artist.mbId" :artistName="album.artist.name" :year="album.year">
      </AnimatedAlbumCover>
    </template>
  </BrowserBase>
</template>

<script setup lang="ts">
import { ref, shallowRef, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { uid } from "quasar";
import { api } from "src/composables/api";
import { getSmallURL } from "src/composables/thumbnail";
import { default as BrowserBase } from 'src/components/BrowserBase.vue';
import { default as AnimatedAlbumCover } from 'src/components/AnimatedAlbumCover.vue';
import {
  type BrowseAlbumsResponse as BrowseAlbumsResponseInterface,
  type BrowseAlbumItemResponse as BrowseAlbumItemResponseInterface,
} from "src/types/api-responses";

const { t } = useI18n();

const currentPageIndex = ref(1);
const totalPages = ref(0);
const totalResults = ref(0);
const warningNoItems = ref(false);
const sortField = ref("Title");
const sortOrder = ref("ASC");
const skipCount = ref(false);
const albums = shallowRef<Album[]>([]);
const loading = ref(false);

class Album implements BrowseAlbumItemResponseInterface {
  _id: string;
  title: string;
  mbId: string | null;
  year: number | null;
  image: string | null;
  artist: {
    name: string | null;
    mbId: string | null;
  }

  constructor(item: BrowseAlbumItemResponseInterface) {
    this._id = uid();
    this.title = item.title;
    this.mbId = item.mbId;
    this.year = item.year;
    this.image = item.mbId ? getSmallURL(`https://coverartarchive.org/release/${item.mbId}/front-250`) : null;
    this.artist = {
      name: null,
      mbId: null,
    };
  }
}

function browse() {
  warningNoItems.value = false;
  loading.value = true;
  api.browse.album({ title: null }, currentPageIndex.value, 32, sortField.value, sortOrder.value, skipCount.value).then((successResponse: BrowseAlbumsResponseInterface) => {
    albums.value = successResponse.data.albums.map((item) => {
      return (new Album(item));
    });

    if (successResponse.data.pager) {
      totalPages.value = successResponse.data.pager.totalPages;
      totalResults.value = successResponse.data.pager.totalResults;
      warningNoItems.value = successResponse.data.pager.totalResults < 1;
      skipCount.value = true;
    }
    loading.value = false;
    /*
    nextTick(() => {
      if (autoFocusRef.value) {
        autoFocusRef.value.focus();
      }
    });
    */
  }).catch((error: Error) => {
    console.error("Error browsing albums", error);
    albums.value = [];
    totalPages.value = 0;
    totalResults.value = 0;
    /*
    $q.notify({
      type: "negative",
      message: t("API Error: error loading albums"),
      caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
    });
    */
    loading.value = false;
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
