<template>
  <q-select outlined dense v-model="model" :options="filteredItems" options-dense :label="label" :disable="disable"
    emit-value filled clearable :hint="!model ? t('Minimum 3 characters to trigger autocomplete') : null" use-input
    input-debounce="0" @filter="onFilter" @update:model-value="onChange">
  </q-select>
</template>

<script setup>
import { ref, reactive } from "vue";
import { useI18n } from "vue-i18n";
const { t } = useI18n();

const props = defineProps({
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
  items: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['change']);

const model = ref(props.defaultValue);

const filteredItems = reactive(props.items);

function onFilter(val, update, abort) {
  if (val.length < 3) {
    abort();
    return;
  }
  update(() => {
    const needle = val.toLowerCase();
    filteredItems = props.items.filter(item => item.toLowerCase().indexOf(needle) > -1);
  });
}

function onChange(selectedValue) {
  emit("change", selectedValue);
}

</script>