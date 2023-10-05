<template>
  <q-select outlined dense v-model="model" :options="options" options-dense :label="t('Sort order')"
    @update:model-value="onChange" :disable="disable">
    <template v-slot:selected-item="scope">
      {{ scope.opt.label }}
    </template>
  </q-select>
</template>

<script setup>

import { ref, computed } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  disable: Boolean,
  order: String
});

const emit = defineEmits(['change']);

// TODO: when changed global locale (from layout menu), selected-item label will not translated until model change (BUG)
const options = computed(() => [
  {
    label: t("Ascending"),
    value: "ASC"
  },
  {
    label: t("Descending"),
    value: "DESC"
  }
]);

const model = ref(options.value[props.order == "DESC" ? 1 : 0]);

function onChange(orderModel) {
  emit("change", orderModel.value);
}

</script>
