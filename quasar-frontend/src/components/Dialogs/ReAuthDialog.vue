<template>
  <BaseDialog v-model="visible" @close="onClose" width="512px" max-width="80vw" hide-footer persistent>
    <template v-slot:header-left>
      {{ t("Session lost... re-auth required") }}
    </template>
    <template v-slot:body>
      <LoginForm :show-extra-bottom="false" @success="onSuccess">
        <template v-slot:slogan>
          <h4 class="q-mt-sm q-mb-md text-h4 text-weight-bolder">{{ t("Oooops") }}</h4>
          <div class="text-color-secondary">
            {{ t("Please enter again your credentials") }}</div>
        </template>
      </LoginForm>
    </template>
  </BaseDialog>
</template>

<script setup>
import { useI18n } from "vue-i18n";

import { default as BaseDialog } from "src/components/Dialogs/BaseDialog.vue"
import { default as LoginForm } from "src/components/Forms/LoginForm.vue"

const { t } = useI18n();

const emit = defineEmits(['close', 'success']);

const visible = ref(true);

const onClose = () => {
  emit('close');
}

const onSuccess = () => {
  emit('success');
};

</script>