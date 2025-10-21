<template>
  <q-tooltip v-if="isDesktop && visibleToolTips" :delay="delay" :anchor="anchor" :self="self">
    <slot></slot>
  </q-tooltip>
</template>

<script setup>

import { computed } from "vue";
import { useQuasar } from "quasar";

import { useLocalStorage } from "src/composables/useLocalStorage";

const $q = useQuasar()

const isDesktop = computed(() => $q.platform.is.desktop);

const { showToolTips } = useLocalStorage();

const visibleToolTips = showToolTips.get();

const props = defineProps({
  delay: {
    type: Number,
    required: false,
    default: 0,
    validator(value) {
      if (value < 0) {
        console.warn('Invalid (negative) delay value');
        return false
      }
      return true
    },
  },
  anchor: {
    type: String,
    required: false,
    default: 'top middle',
  },
  self: {
    type: String,
    required: false,
    default: 'bottom middle',
  },
})

</script>