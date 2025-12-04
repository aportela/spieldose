<template>
  <q-input :type="visiblePassword ? 'text' : 'password'" v-model="password" :dense="dense" :label="label" :rules="rules"
    :outlined="outlined" v-bind="attrs" ref="qInputPasswordRef" :error="error" :error-message="errorMessage">
    <template v-slot:prepend>
      <q-icon name="key" />
    </template>
    <template v-slot:append v-if="password">
      <q-icon :name="visibilityIcon" class="cursor-pointer" @click="visiblePassword = !visiblePassword"
        :aria-label="t(tooltipLabel)" />
      <DesktopToolTip anchor="bottom right" self="top end" :aria-label="t(tooltipLabel)">{{ t(tooltipLabel)
        }}</DesktopToolTip>
    </template>
  </q-input>
</template>

<script setup lang="ts">

import { ref, computed, useAttrs, onMounted, nextTick } from "vue";
import { useI18n } from "vue-i18n";
import { QInput } from "quasar";

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

const attrs = useAttrs();

const { t } = useI18n();

const emit = defineEmits(['update:modelValue'])

interface PasswordFieldCustomInputProps {
  modelValue: string;
  label?: string;
  dense?: boolean;
  outlined?: boolean;
  rules?: Array<(val: string) => boolean | string>;
  autofocus?: boolean;
  error?: boolean;
  errorMessage?: string;
};

const props = withDefaults(defineProps<PasswordFieldCustomInputProps>(), {
  label: "Password",
  dense: false,
  outlined: false,
  rules: () => [],
  autofocus: false,
  error: false,
  errorMessage: ""
});

const qInputPasswordRef = ref<QInput | null>(null);

const visiblePassword = ref(false);

const password = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const visibilityIcon = computed(() => visiblePassword.value ? "visibility_off" : "visibility");
const tooltipLabel = computed(() => visiblePassword.value ? "Hide password" : "Show password");

const focus = () => {
  nextTick()
    .then(() => {
      qInputPasswordRef.value?.focus();
    }).catch((e) => {
      console.error(e);
    });
}

const resetValidation = () => {
  qInputPasswordRef.value?.resetValidation();
}

const validate = () => {
  return (qInputPasswordRef.value?.validate() === true);
}

defineExpose({
  focus, resetValidation, validate
});

onMounted(() => {
  if (props.autofocus) {
    focus();
  }
});

</script>