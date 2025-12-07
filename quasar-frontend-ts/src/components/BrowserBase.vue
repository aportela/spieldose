<template>
  <q-card class="q-pa-lg" v-bind="attrs">
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
import { computed, useAttrs } from "vue";

const emit = defineEmits(['paginationChanged']);

const attrs = useAttrs();

interface BrowserBaseProps {
  disable?: boolean;
  currentPageIndex?: number;
  totalPages?: number;
  totalResults?: number;
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