<template>
  <q-select v-bind="attrs" :options="sortOrderSelectorOptions" v-model="model" :label="label">
  </q-select>
</template>

<script setup lang="ts">
import { useAttrs, computed } from 'vue';
import { type SelectorOption, sortOrderSelectorOptions } from 'src/types/common';

interface SortSelectorProps {
  modelValue: SelectorOption,
  label?: string
}

const emit = defineEmits(['update:modelValue'])

const props = defineProps<SortSelectorProps>();

const attrs = useAttrs();

const model = computed({
  get() {
    return (props.modelValue);
  },
  set(value) {
    emit("update:modelValue", value);
  }
});

</script>