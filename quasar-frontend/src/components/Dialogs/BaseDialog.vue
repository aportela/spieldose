<template>
  <q-dialog v-model="visible" :persistent="persistent" @hide="onHide" @show="onShow">
    <q-card class="q-card-base-dialog" :style="{ width: width, minWidth: minWidth, maxWidth: maxWidth }">
      <q-card-section class="q-py-xs row self-center q-dialog-header">
        <slot name="header">
          <div class="col flex items-center">
            <slot name="header-left"></slot>
          </div>
          <q-space />
          <div>
            <slot name="header-right"></slot>
            <q-btn icon="close" flat round dense v-close-popup aria-label="Close modal"
              v-if="showHeaderCloseButton && !persistent" />
          </div>
        </slot>
      </q-card-section>
      <q-card-section class="q-pa-none q-dialog-body">
        <slot name="body"></slot>
      </q-card-section>
      <q-card-section class="q-pa-none q-dialog-footer" v-if="!hideFooter">
        <slot name="footer">
          <q-card-actions align="right">
            <slot name="actions">
              <slot name="actions-prepend"></slot>
              <q-btn class="action-secondary" v-if="confirmationDialog" @click.stop="onCancel" :disable="disable"
                icon="close" :label="t('Cancel')" />
              <q-btn color="primary" v-if="confirmationDialog" @click.stop="onConfirm" :disable="disable" icon="done"
                :label="t('Ok')" />
              <q-btn color="primary" v-if="!confirmationDialog" size="md" no-caps @click.stop="onHide"
                :disable="disable || persistent" icon="close" :label="t('Close')" />
            </slot>
          </q-card-actions>
        </slot>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>

import { computed } from "vue";

import { useI18n } from "vue-i18n";

const { t } = useI18n();

const emit = defineEmits(['update:modelValue', 'show', 'close', 'confirm', 'cancel']);

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  showHeaderCloseButton: {
    type: Boolean,
    required: false,
    default: true
  },
  width: {
    type: String,
    required: false,
    default: ""
  },
  minWidth: {
    type: String,
    required: false,
    default: ""
  },
  maxWidth: {
    type: String,
    required: false,
    default: ""
  },
  confirmationDialog: {
    type: Boolean,
    required: false,
    default: false
  },
  disable: {
    type: Boolean,
    required: false,
    default: false
  },
  persistent: {
    type: Boolean,
    required: false,
    default: false
  },
  hideFooter: {
    type: Boolean,
    required: false,
    default: false
  }
});

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const onShow = () => {
  emit('show');
};

const onHide = () => {
  visible.value = false;
  emit('close');
}

const onConfirm = () => {
  visible.value = false;
  emit('confirm');
}

const onCancel = () => {
  visible.value = false;
  emit('cancel');
}

</script>