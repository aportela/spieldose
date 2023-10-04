<template>
  <q-select outlined dense v-model="model" :options="options" options-dense :label="t('Sort order')"
    @update:model-value="onChange">
    <template v-slot:selected-item="scope">
      {{ t(scope.opt.label) }}
    </template>
  </q-select>
</template>

<script setup>

import { ref } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  order: String
});

const emit = defineEmits(['change']);

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

const model = ref(options[props.order == "DESC" ? 1 : 0]);

function onChange(orderModel) {
  emit("change", orderModel.value);
}

</script>
