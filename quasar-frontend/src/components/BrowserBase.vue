<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <slot name="breacrumb"></slot>
    </q-breadcrumbs>
    <q-card-section>
      <slot name="filter"></slot>
      <div v-if="totalResults > 0">
        <div class="flex flex-center" v-if="totalPages > 1">
          <q-pagination v-model="currentPage" color="dark" :max="totalPages || 1" :max-pages="5" boundary-numbers
            direction-links boundary-links @update:model-value="onPaginationChanged" :disable="disable" />
        </div>
        <div class="q-mt-md q-gutter-md row items-start">
          <slot name="items"></slot>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref } from "vue";

const emit = defineEmits(['paginationChanged']);

const props = defineProps({
  disable: Boolean,
  currentPageIndex: Number,
  totalPages: Number,
  totalResults: Number
});

const currentPage = ref(props.currentPageIndex || 1);

function onPaginationChanged(page) {
  emit("paginationChanged", page)
}

</script>
