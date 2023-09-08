<template>
  <component :is="dashboardBaseBlock" :icon="icon || 'format_list_numbered'" :title="title" :loading="loading"
    @refresh="refresh">
    <template #tabs>
      <q-tabs v-model="tab" no-caps class="text-pink-7 q-mb-md">
        <q-tab v-for="tabElement in entities" :key="tabElement.value" :name="tabElement.value"
          :label="t(tabElement.label)" />
      </q-tabs>
    </template>
    <template #list>
      <ol class="q-px-sm" v-if="tab == 'tracks'">
        <DashboardBaseBlockListElementTrack v-for="item in items" :key="item.id" :track="item.track">
          <template #append v-if="played">
            <LabelTimestampAgo className="q-ml-sm" :timestamp="item.lastPlayTimestamp * 1000">
              <template #prepend>(</template>
              <template #append>)</template>
            </LabelTimestampAgo>
          </template>
          <template #append v-else-if="added">
            <LabelTimestampAgo className="q-ml-sm" :timestamp="item.addedTimestamp * 1000">
              <template #prepend>(</template>
              <template #append>)</template>
            </LabelTimestampAgo>
          </template>
        </DashboardBaseBlockListElementTrack>
      </ol>
      <ol class="q-px-sm" v-else-if="tab == 'artists'">
        <DashboardBaseBlockListElementArtist v-for="item in items" :key="item.id" :artist="item">
          <template #append v-if="played">
            <LabelTimestampAgo className="q-ml-sm" :timestamp="item.lastPlayTimestamp * 1000">
              <template #prepend>(</template>
              <template #append>)</template>
            </LabelTimestampAgo>
          </template>
          <template #append v-else-if="added">
            <LabelTimestampAgo className="q-ml-sm" :timestamp="item.addedTimestamp * 1000">
              <template #prepend>(</template>
              <template #append>)</template>
            </LabelTimestampAgo>
          </template>
        </DashboardBaseBlockListElementArtist>
      </ol>
      <ol class="q-px-sm" v-else-if="tab == 'albums'">
        <DashboardBaseBlockListElementAlbum v-for="item in items" :key="item.id" :album="item">
          <template #append v-if="played">
            <LabelTimestampAgo className="q-ml-sm" :timestamp="item.lastPlayTimestamp * 1000">
              <template #prepend>(</template>
              <template #append>)</template>
            </LabelTimestampAgo>
          </template>
          <template #append v-else-if="added">
            <LabelTimestampAgo className="q-ml-sm" :timestamp="item.addedTimestamp * 1000">
              <template #prepend>(</template>
              <template #append>)</template>
            </LabelTimestampAgo>
          </template>
        </DashboardBaseBlockListElementAlbum>
      </ol>
      <ol class="q-px-sm" v-else-if="tab == 'genres'">
        <DashboardBaseBlockListElementGenre v-for="item in items" :key="item.name" :genre="item">
          <template #append v-if="played">
            <LabelTimestampAgo className="q-ml-sm" :timestamp="item.lastPlayTimestamp * 1000">
              <template #prepend>(</template>
              <template #append>)</template>
            </LabelTimestampAgo>
          </template>
          <template #append v-else-if="added">
            <LabelTimestampAgo className="q-ml-sm" :timestamp="item.addedTimestamp * 1000">
              <template #prepend>(</template>
              <template #append>)</template>
            </LabelTimestampAgo>
          </template>
        </DashboardBaseBlockListElementGenre>
      </ol>
      <h5 class="text-h5 text-center q-py-sm q-mt-xl q-mt-sm" v-if="!loading && !(items && items.length > 0)"><q-icon name="warning"
          size="xl"></q-icon> {{ t('No enought data') }}</h5>
    </template>
  </component>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useQuasar } from "quasar";
import { useI18n } from 'vue-i18n'
import { default as dashboardBaseBlock } from 'components/DashboardBaseBlock.vue';
import { default as DashboardBaseBlockListElementTrack } from 'components/DashboardBaseBlockListElementTrack.vue';
import { default as DashboardBaseBlockListElementArtist } from 'components/DashboardBaseBlockListElementArtist.vue';
import { default as DashboardBaseBlockListElementAlbum } from 'components/DashboardBaseBlockListElementAlbum.vue';
import { default as DashboardBaseBlockListElementGenre } from 'components/DashboardBaseBlockListElementGenre.vue';
import { default as LabelTimestampAgo } from "components/LabelTimestampAgo.vue";
import { api } from 'boot/axios';

const $q = useQuasar();
const { t } = useI18n();

const loading = ref(false);
const items = ref([]);

const title = computed(() => {
  let str = null;
  if (props.played) {
    str = t('Recently played');
  } else if (props.added) {
    str = t('Recently added');
  }
  return (str);
});

const tab = ref(null);

watch(tab, () => {
  refresh();
});

const entities = [
  {
    label: 'Tracks',
    value: 'tracks',
    function: api.metrics.getTracks,
    listItemType: ''
  },
  {
    label: 'Artists',
    value: 'artists',
    function: api.metrics.getArtists,

  },
  {
    label: 'Albums',
    value: 'albums',
    function: api.metrics.getAlbums,
  },
  {
    label: 'Genres',
    value: 'genres',
    function: api.metrics.getGenres,
  }
];

const props = defineProps({
  icon: {
    type: String
  },
  played: {
    type: Boolean
  },
  added: {
    type: Boolean
  },
  globalStats: Boolean
})


const useGlobalStats = computed(() => {
  return(props.globalStats || false);
});

watch(useGlobalStats, (newValue) => {
  if (props.played) {
    refresh();
  }
});

const count = 5;
let filter = {
  entity: 'tracks'
};

function refresh() {
  if (tab.value) {
    items.value = [];
    const funct = entities.filter((entity) => entity.value == tab.value)[0].function;
    loading.value = true;
    let sortField = null;
    if (props.played) {
      sortField = 'recentlyPlayed';
    } else if (props.added) {
      sortField = 'recentlyAdded'
    }
    funct(filter, sortField, count).then((success) => {
      items.value = success.data.data;
      loading.value = false;
    }).catch((error) => {
      loading.value = false;
      $q.notify({
        type: "negative",
        message: "API Error: error loading metrics",
        caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
      });
    });
  }
}

tab.value = 'tracks';

</script>
