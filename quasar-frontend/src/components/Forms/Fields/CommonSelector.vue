<template>
  <q-select :outlined="outlined" :dense="dense" v-model="model" :options="filteredOptions" :options-dense="dense"
    :label="label" :disable="disable" map-options emit-value :filled="filled" :clearable="clearable"
    :hint="!model ? t('Minimum 3 characters to trigger autocomplete') : null" :use-input="useAutocomplete"
    input-debounce="100" @filter="onFilter" @update:model-value="onChange">
  </q-select>
</template>

<script setup>
import { ref, reactive } from "vue";
import { useI18n } from "vue-i18n";
const { t } = useI18n();

const props = defineProps({
  dense: {
    type: Boolean,
    required: false,
    default: true
  },
  outlined: {
    type: Boolean,
    required: false,
    default: true
  },
  filled: {
    type: Boolean,
    required: false,
    default: true
  },
  clearable: {
    type: Boolean,
    required: false,
    default: true
  },
  disable: {
    type: Boolean,
    required: false,
    default: false
  },
  defaultValue: {
    type: String,
    required: false,
    default: null
  },
  label: {
    type: String,
    required: true
  },
  options: {
    type: Array,
    required: true
  },
  useAutocomplete: {
    type: Boolean,
    required: false,
    default: true
  },
});

const emit = defineEmits(['change']);

const model = ref(null);

const filteredOptions = reactive([...props.options]);

function onFilter(val, update, abort) {
  if (!props.useAutocomplete || val.length < 3) {
    abort();
    return;
  }
  update(() => {
    const needle = val.toLowerCase();
    filteredOptions.length = 0;
    filteredOptions.push(...props.options.filter(item => item.label.toLowerCase().includes(needle)));
  });
}

function onChange(selectedValue) {
  emit("change", selectedValue);
}

</script>
