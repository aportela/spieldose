<template>
  <q-tooltip v-if="isDesktop && sessionStore.toolTipsEnabled" :delay="delay" :anchor="anchor" :self="self">
    <slot></slot>
  </q-tooltip>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useQuasar } from "quasar";

import { useSessionStore } from "src/stores/session";

const $q = useQuasar()

const isDesktop = computed(() => $q.platform.is.desktop);

const sessionStore = useSessionStore();

type TooltipAnchor =
  | 'top left' | 'top middle' | 'top right' | 'top start' | 'top end'
  | 'center left' | 'center middle' | 'center right' | 'center start' | 'center end'
  | 'bottom left' | 'bottom middle' | 'bottom right' | 'bottom start' | 'bottom end';

type TooltipSelf = TooltipAnchor;

interface TooltipProps {
  delay?: number;
  anchor?: TooltipAnchor;
  self?: TooltipSelf;
};

withDefaults(defineProps<TooltipProps>(), {
  delay: 0,
  anchor: 'top middle',
  self: 'bottom middle',
});
</script>
