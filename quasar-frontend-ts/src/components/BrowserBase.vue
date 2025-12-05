<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <slot name="current-breadcrumb">
        <q-breadcrumbs-el v-if="currentBreadCrumbIcon && currentBreadCrumbLabel" :icon="currentBreadCrumbIcon"
          :label="currentBreadCrumbLabel" />
      </slot>
    </q-breadcrumbs>
    <q-card-section>
      <slot name="filter"></slot>
      <div v-if="totalResults > 0">
        <div class="flex flex-center" v-if="totalPages > 1">
          <q-pagination v-model="currentPage" color="dark" :max="totalPages || 1" :max-pages="5" boundary-numbers
            direction-links boundary-links :disable="disable" />
        </div>
        <div class="q-mt-md q-gutter-md row items-start">
          <slot name="items"></slot>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import { computed } from "vue";

const emit = defineEmits(['paginationChanged']);

interface BrowserBaseProps {
  disable?: boolean;
  currentPageIndex?: number;
  totalPages?: number;
  totalResults?: number;
  currentBreadCrumbIcon: string;
  currentBreadCrumbLabel: string;
};

const props = withDefaults(defineProps<BrowserBaseProps>(), {
  disable: false,
  currentPageIndex: 1,
  totalPages: 0,
  totalResults: 0,
});

const currentPage = computed({
  get() {
    return (props.currentPageIndex);
  },
  set(value) {
    emit("paginationChanged", value)
  }
});
</script>