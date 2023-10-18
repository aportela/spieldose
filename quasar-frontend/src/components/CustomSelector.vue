<template>
  <q-select outlined dense v-model="value" :options="options" options-dense :label="label ? t(label): null" :disable="disable">
    <template v-slot:option="scope">
      <q-item v-bind="scope.itemProps">
        <q-item-section>
          <q-item-label>{{ t(scope.opt.label) }}</q-item-label>
        </q-item-section>
      </q-item>
    </template>
    <template v-slot:selected-item>
      {{ t(selectedItemLabel) }}
    </template>
  </q-select>
</template>

<script setup>

import { ref, computed } from "vue";
import { useI18n } from "vue-i18n";

const props = defineProps(['label', 'options', 'modelValue', 'disable']);
const emit = defineEmits(['update:modelValue']);

const { t } = useI18n();

const selectedItemLabel = ref((props.options.find((option) => option.value == props.modelValue) || props.options[0]).label);

const value = computed({
  get() {
    return (props.modelValue);
  },
  set(option) {
    selectedItemLabel.value = option.label;
    emit('update:modelValue', option.value);
  }
});

</script>
