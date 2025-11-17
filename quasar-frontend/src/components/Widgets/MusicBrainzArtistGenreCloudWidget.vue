<template>
  <CustomExpansionWidget title="MusicBrainz artist genre cloud" icon="genre" icon-tool-tip="Click to refresh data"
    :on-header-icon-click="onRefresh" :loading="state.loading" :error="state.loadingError" :expanded="isExpanded"
    @expand="isExpanded = true" @collapse="isExpanded = false">
    <template v-slot:header-extra>
      <q-chip square size="sm" color="grey-7" text-color="white">{{ t("Total genres", {
        count:
          genres.length
      }) }}</q-chip>
    </template>
    <template v-slot:content>
      <div class="row items-center q-gutter-sm q-mt-none q-pa-xs" v-if="state.loading">
        <q-skeleton square width="12em" height="2em" class="" v-for="j in 32" :key="j"></q-skeleton>
      </div>
      <CustomErrorBanner v-else-if="state.loadingError" :text="state.errorMessage || 'Error loading data'"
        :api-error="state.apiError">
      </CustomErrorBanner>
      <div v-else-if="hasGenres">
        <BrowseByMusicBrainzArtistGenreButton v-for="genre in genres" :key="genre.name" :genre="genre.name"
          :caption="String(genre.total)" :q-chip-class="'q-chip-10em'"></BrowseByMusicBrainzArtistGenreButton>
      </div>
      <CustomBanner v-else warning text="No genres found"></CustomBanner>
    </template>
  </CustomExpansionWidget>
</template>

<script setup>

import { ref, reactive, computed, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import { useBus } from "src/composables/useBus";
import { useAPI } from "src/composables/useAPI";

import { default as CustomExpansionWidget } from "src/components/Widgets/CustomExpansionWidget.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";
import { default as BrowseByMusicBrainzArtistGenreButton } from "src/components/Buttons/BrowseByMusicBrainzArtistGenreButton.vue";

const { t } = useI18n();
const { api } = useAPI();
const { bus } = useBus();

const props = defineProps({
  expanded: {
    type: Boolean,
    required: false,
    default: true
  }
});

const isExpanded = ref(props.expanded);

const state = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null
});

const genres = reactive([]);
const hasGenres = computed(() => genres.length > 0);

const onRefresh = () => {
  if (!state.loading) {
    state.loading = true;
    state.loadingError = false;
    state.errorMessage = null;
    state.apiError = null;
    api.cloud.getMusicBrainzArtistGenreCloud()
      .then((successResponse) => {
        genres.length = 0;
        genres.push(...successResponse.data.items);
        state.loading = false;
      })
      .catch((errorResponse) => {
        state.loadingError = true;
        if (errorResponse.isAPIError) {
          switch (errorResponse.response.status) {
            case 401:
              state.apiError = errorResponse.customAPIErrorDetails;
              state.errorMessage = "Auth session expired, requesting new...";
              bus.emit("reAuthRequired", { emitter: "TagCloudWidget" });
              break;
            default:
              state.apiError = errorResponse.customAPIErrorDetails;
              state.errorMessage = "API Error: fatal error";
              break;
          }
        } else {
          state.errorMessage = `Uncaught exception: ${errorResponse}`;
          console.error(errorResponse);
        }
        state.loading = false;
      });
  }
};

onMounted(() => {
  onRefresh();
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("TagCloudWidget")) {
      onRefresh();
    }
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthSucess");
});

</script>

<style lang="css">
.q-chip-10em {
  width: 10em;
}
</style>