<template>
  <q-card class="full-width">
    <q-item class="theme-default-q-card-section-header">
      <q-item-section avatar>
        <q-icon v-if="loading" name="settings" class="animation-spin"></q-icon>
        <q-icon v-else-if="error" name="error" color="red" @click.stop="onHeaderIconClicked" :class="iconClass">
          <DesktopToolTip v-if="iconToolTip">{{ t(iconToolTip) }}</DesktopToolTip>
        </q-icon>
        <q-icon v-else :name="icon" @click.stop="onHeaderIconClicked" :class="iconClass">
          <DesktopToolTip v-if="iconToolTip">{{ t(iconToolTip) }}</DesktopToolTip>
        </q-icon>
      </q-item-section>
      <q-item-section v-if="title || caption">
        <q-item-label v-if="title">{{ t(title) }}</q-item-label>
        <q-item-label v-if="caption" caption>{{ t(caption) }}</q-item-label>
      </q-item-section>
    </q-item>
    <q-separator />
    <q-card-section>
      <slot name="content"></slot>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">

import { computed } from "vue";

import { useI18n } from "vue-i18n";

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

const { t } = useI18n();

interface BaseWidgetProps {
  title: string;
  caption?: string;
  icon: string;
  iconToolTip?: string;
  onHeaderIconClick?: (() => void) | null;
  loading?: boolean;
  error?: boolean;
};
const props = withDefaults(defineProps<BaseWidgetProps>(), {
  caption: '',
  iconToolTip: '',
  onHeaderIconClick: null,
  loading: false,
  error: false
});

const iconClass = computed(() => props.onHeaderIconClick && typeof props.onHeaderIconClick === 'function' ? "cursor-pointer" : "cursor-default");

const onHeaderIconClicked = () => {
  if (props.onHeaderIconClick && typeof props.onHeaderIconClick === 'function') {
    props.onHeaderIconClick();
  }
}

</script>