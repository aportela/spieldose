<template>
  <BaseWidget :title="title" :icon="icon" :loading="loading" :error="error" :onHeaderIconClick="onRefresh">
    <template v-slot:content>
      <div class="row overflow-hidden" v-for="row, rowIndex in rows" :key="row">
        <div :class="{ 'col-12': columns == 1, 'col-6': columns == 2, 'col-4': columns == 3, 'col-3': columns == 4 }"
          v-for="column, columnIndex in columns" :key="column">
          <img class="spieldose-album-cover-tile" :src="images[(rows * rowIndex) + columnIndex]"
            v-if="images.length > 0" @error="onImageError($event)">
          <img class="spieldose-album-cover-tile" :src="defaultImage" v-else>
        </div>
      </div>
    </template>
  </BaseWidget>
</template>

<script setup>

import { ref, computed, onMounted } from "vue";

import { useI18n } from "vue-i18n";
import { useAPI } from "src/composables/useAPI";

import { default as BaseWidget } from "src/components/Widgets/BaseWidget.vue";

const { t } = useI18n();

const { api } = useAPI();

const props = defineProps({
  columns: {
    type: Number,
    required: false,
    default: 3,
    validator(value) {
      return value > 0 && value < 5;
    }
  },
  rows: {
    type: Number,
    required: false,
    default: 3,
    validator(value) {
      return value > 0 && value < 5;
    }
  },
  discoverTypeNew: {
    type: Boolean,
    required: false,
    default: false
  },
  discoverTypeRandom: {
    type: Boolean,
    required: false,
    default: false
  },
  discoverTypeFeatured: {
    type: Boolean,
    required: false,
    default: false
  },
  discoverTypeRecommended: {
    type: Boolean,
    required: false,
    default: false
  },
});

const title = computed(() => {
  if (props.discoverTypeNew) {
    return (t("New albums"));
  } else if (props.discoverTypeRandom) {
    return (t("Random albums"));
  } else if (props.discoverTypeFeatured) {
    return (t("Featured albums"));
  } else if (props.discoverTypeRecommended) {
    return (t("Recommended albums for you"));
  } else {
    return (t("None"));
  }
});

const icon = computed(() => {
  if (props.discoverTypeNew) {
    return ("fiber_new");
  } else if (props.discoverTypeRandom) {
    return ("shuffle");
  } else if (props.discoverTypeFeatured) {
    return ("workspace_premium");
  } else if (props.discoverTypeRecommended) {
    return ("recommend");
  } else {
    return ("warning");
  }
});

const loading = ref(false);
const error = ref(false);

const images = ref([]);

const defaultImage = 'images/vinyl-medium.png';

function onImageError(event) {
  event.target.src = defaultImage;
}

const onRefresh = () => {
  error.value = false;
  loading.value = true;
  api.album.getSmallRandomCovers(144).then(response => {
    images.value = Array.isArray(response.data.coverURLs) ? response.data.coverURLs : [];
    loading.value = false;
  }).catch(error => {
    console.error(error.response);
    loading.value = false;
    error.value = true;
  });
};

onMounted(() => {
  onRefresh();
});

</script>

<style lang="css" scoped>
img.spieldose-album-cover-tile {
  width: 100%;
  max-width: 100%;
  height: 100%;
  aspect-ratio: 1 / 1;
  padding: 4px;
}
</style>
