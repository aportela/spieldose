<template>
  <q-select outlined dense v-model="model" :options="options" options-dense :label="t('Sort field')"
    @update:model-value="onChange" :disable="disable">
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
  disable: Boolean,
  options: Array,
  field: String
});

const emit = defineEmits(['change']);

const model = ref(props.options.find((option) => option.value == props.field) || props.options[0]);

function onChange(fieldModel) {
  emit("change", fieldModel.value);
}

</script>
