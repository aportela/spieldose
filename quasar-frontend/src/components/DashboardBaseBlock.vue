<template>
  <q-card class="my-card q-ma-md">
    <q-card-section class="bg-grey-3 text-black">
      <div class="text-h6 row">
        <div class="col text-left">
          <q-icon :name="icon" class="cursor-pointer q-mr-sm"></q-icon>{{ title }}
        </div>
        <div class="col text-right">
          <q-spinner v-if="loading" color="pink" size="sm" class="q-ml-sm" :thickness="8" />
          <q-icon name="refresh" class="cursor-pointer" @click="onRefresh" v-else :title="t('reload')"></q-icon>
        </div>
      </div>
    </q-card-section>
    <q-card-section class="bg-white text-black" style="min-height: 17em;">
      <slot name="body">
        <q-skeleton type="text" square animation="blink" height="198px" v-if="loading" />
        <div v-else>
          <slot name="prepend"></slot>
          <slot name="tabs"></slot>
          <slot name="list"></slot>
          <slot name="chart"></slot>
        </div>
      </slot>
    </q-card-section>

  </q-card>
</template>

<script setup>

import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  className: {
    type: String
  },
  loading: {
    type: Boolean,
  },
  icon: {
    type: String
  },
  title: {
    type: String
  }
});

const emit = defineEmits(['refresh']);

function onRefresh() {
  emit('refresh');
}

</script>
