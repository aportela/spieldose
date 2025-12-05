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
import { ref, shallowRef, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { uid } from "quasar";
import { api } from "src/composables/api";
import { default as BrowserBase } from "src/components/BrowserBase.vue";
import { default as ArtistAvatarLink } from "src/components/ArtistAvatarLink.vue"

const { t } = useI18n();

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
  warningNoItems.value = false;
  loading.value = true;
  api.browse.artist({ genre: null, tag: null, name: null }, currentPageIndex.value, 32, sortField.value, sortOrder.value, skipCount.value).then((success) => {
    // create unique id (name can not be used because there are some items with same name but different mbId, like Alice Cooper (artist) && Alice Cooper (band))
    artists.value = success.data.data.items.map((item) => { item._id = uid(); return (item); });
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
    artists.value = [];
    totalPages.value = 0;
    totalResults.value = 0;
    /*
    $q.notify({
      type: "negative",
      message: t("API Error: error loading artists"),
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