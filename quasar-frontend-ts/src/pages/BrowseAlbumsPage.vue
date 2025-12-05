<template>
  <BrowserBase current-bread-crumb-icon="album" :current-bread-crumb-label="t('Browse albums')" :disable="loading"
    :currentPageIndex="currentPageIndex" :totalPages="totalPages" :totalResults="totalResults"
    @paginationChanged="onPaginationChanged">
    <template #items>
      <AnimatedAlbumCover v-for="album in albums" :key="album._id" v-memo="[lastChangesTimestamp]" :image="album.image"
        :title="album.title" :albumMbId="album.mbId" :artistMbId="album.artist.mbId" :artistName="album.artist.name"
        :year="album.year">
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

const { t } = useI18n();

const currentPageIndex = ref(1);
const totalPages = ref(0);
const totalResults = ref(0);
const warningNoItems = ref(false);
const sortField = ref(null);
const sortOrder = ref(null);
const skipCount = ref(false);
const albums = shallowRef([]);
const loading = ref(false);

function browse() {
  warningNoItems.value = false;
  loading.value = true;
  api.browse.album({ title: null }, currentPageIndex.value, 32, sortField.value, sortOrder.value, skipCount.value).then((success) => {
    albums.value = success.data.data.items.map((item) => { item._id = uid(); item.artist = { mbId: null, name: null }; item.image = getSmallURL(`https://coverartarchive.org/release/${item.mbId}/front-250`); return (item); });
    if (success.data.data.pager) {
      totalPages.value = success.data.data.pager.totalPages;
      totalResults.value = success.data.data.pager.totalResults;
      warningNoItems.value = success.data.data.pager.totalResults < 1;
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
  }).catch((error) => {
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