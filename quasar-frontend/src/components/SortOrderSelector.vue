<template>
  <CustomSelector label="Sort order" :options="options" v-model="value" :disable="disable"></CustomSelector>
</template>

<script setup>

import { computed } from "vue";

import { default as CustomSelector } from "components/CustomSelector.vue";

const props = defineProps(['modelValue', 'disable']);
const emit = defineEmits(['update:modelValue']);

const options = [
  {
    label: "Ascending",
    value: "ASC"
  },
  {
    label: "Descending",
    value: "DESC"
  }
];

const value = computed({
  get() {
    return (props.modelValue);
  },
  set(val) {
    emit('update:modelValue', val);
  }
});

</script>
