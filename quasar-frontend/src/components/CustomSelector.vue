<template>
  <q-select outlined dense v-model="model" :options="options" options-dense :label="label" @update:model-value="onChange"
    :disable="disable">
    <template v-slot:selected-item="scope">
      {{ scope.opt.label }}
    </template>
  </q-select>
</template>

<script setup>

import { ref } from "vue";

const props = defineProps({
  disable: Boolean,
  label: String,
  options: Array,
  field: String
});

const emit = defineEmits(['change']);

const model = ref(props.options && Array.isArray(props.options) && props.options.length > 0 ? (props.options.find((option) => option.value == props.field) || props.options[0]): null);

function onChange(fieldModel) {
  emit("change", fieldModel.value);
}

</script>
