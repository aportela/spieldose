<template>
  <q-input v-model="value" clearable type="search" outlined dense :placeholder="t(placeholder)" :hint="t(hint)"
    :loading="loading" :disable="disable" @keydown.enter.prevent="onSubmit" @clear="onClear"
    :error="error" :errorMessage="t(errorMessage)" ref="inputRef">
    <template v-slot:prepend>
      <q-icon name="filter_alt" />
    </template>
    <template v-slot:append>
      <q-icon name="search" class="cursor-pointer" @click="onSubmit" />
    </template>
  </q-input>
</template>

<script setup>

import { ref, computed } from "vue";
import { useI18n } from "vue-i18n";

const props = defineProps(['hint', 'placeholder', 'error', 'errorMessage', 'modelValue', 'disable', 'loading']);
const emit = defineEmits(['update:modelValue', 'submit']);

const { t } = useI18n();

const inputRef = ref(null);

const value = computed({
  get() {
    return (props.modelValue);
  },
  set(val) {
    emit('update:modelValue', val);
  }
});

function onSubmit() {
  emit('submit');
}

function onClear() {
  value.value = null;
  emit('clear');
}

const focus = () => {
  inputRef.value.focus();
}

defineExpose({ focus });

</script>
