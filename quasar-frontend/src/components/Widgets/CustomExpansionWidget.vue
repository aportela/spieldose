<template>
  <q-expansion-item class="full-width theme-default-q-expansion-item" style="width: 100%"
    header-class="theme-default-q-expansion-item-header" v-model="isExpanded">
    <template v-slot:header>
      <q-item-section avatar>
        <q-icon v-if="loading && !staticIcon" name="settings" class="animation-spin"></q-icon>
        <q-icon v-else-if="error && !staticIcon" name="error" color="red" @click.stop="onHeaderIconClicked"
          :class="iconClass">
          <DesktopToolTip v-if="iconToolTip">{{ t(iconToolTip) }}</DesktopToolTip>
        </q-icon>
        <q-icon v-else :name="icon" @click.stop.prevent="onHeaderIconClicked" :class="iconClass">
          <DesktopToolTip v-if="iconToolTip">{{ t(iconToolTip) }}</DesktopToolTip>
        </q-icon>
      </q-item-section>
      <q-item-section>
        <q-item-label>{{ t(title) }}
          <slot name="header-extra">
          </slot>
        </q-item-label>
        <q-item-label caption v-if="caption">{{ t(caption) }}</q-item-label>
      </q-item-section>
    </template>
    <q-card class="q-ma-xs" flat>
      <q-card-section class="q-pa-none">
        <slot name="content"></slot>
      </q-card-section>
    </q-card>
  </q-expansion-item>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

const { t } = useI18n();

const emit = defineEmits(['expand', 'collapse']);

const props = defineProps({
  expanded: {
    type: Boolean,
    required: false,
    default: true
  },
  title: {
    type: String,
    required: false,
    default: ""
  },
  caption: {
    type: String,
    required: false,
    default: ""
  },
  icon: {
    type: String,
    required: true
  },
  staticIcon: {
    type: Boolean,
    required: false,
    default: false
  },
  iconToolTip: {
    type: String,
    required: false
  },
  onHeaderIconClick: {
    type: Function,
    default: null
  },
  loading: {
    type: Boolean,
    required: false
  },
  error: {
    type: Boolean,
    required: false
  }
});

const isExpanded = ref(props.expanded === true);
const iconClass = computed(() => !!props.onHeaderIconClick ? "cursor-pointer" : "cursor-default");

watch(() => isExpanded.value, val => {
  if (val) {
    emit('expand');
  } else {
    emit('collapse');
  }
});

const expand = () => {
  isExpanded.value = true;
};

const collapse = () => {
  isExpanded.value = false;
};

defineExpose({
  expand, collapse
});

const onHeaderIconClicked = () => {
  if (props.onHeaderIconClick && typeof props.onHeaderIconClick === 'function') {
    props.onHeaderIconClick();
  }
}

</script>
