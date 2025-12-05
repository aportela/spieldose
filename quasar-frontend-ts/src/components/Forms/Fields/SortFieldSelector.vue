<template>
  <q-select v-bind="attrs" :options="options" v-model="model" :label="label">
  </q-select>
</template>

<script setup lang="ts">
import { useAttrs, computed } from 'vue';

interface OptionInterface {
  label: string;
  value: string;
}

interface SortFieldSelector {
  modelValue: OptionInterface,
  label?: string;
  options: OptionInterface[];
}

const emit = defineEmits(['update:modelValue'])

const props = defineProps<SortFieldSelector>();

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