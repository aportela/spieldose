<template>
  <BaseWidget title="Personal information" caption="Update your data" icon="contact_mail">
    <template v-slot:content>
      <form @submit.prevent.stop="onValidateForm" autocorrect="off" autocapitalize="off" autocomplete="off"
        spellcheck="false">
        <q-input class="q-my-md" dense outlined v-model="profile.email" type="email" name="email" :label="t('Email')"
          :disable="state.loading" :error="validator.email.hasErrors"
          :error-message="validator.email.message ? t(validator.email.message) : ''"
          :rules="formUtils.requiredFieldRules" lazy-rules ref="emailRef">
          <template v-slot:prepend>
            <q-icon name="alternate_email" />
          </template>
        </q-input>
        <PasswordFieldCustomInput class="q-my-md" dense outlined v-model="profile.password" name="password"
          :label="t('New password')" :error="validator.password.hasErrors"
          :error-message="validator.password.message ? t(validator.password.message) : ''" :disable="state.loading"
          :rules="formUtils.requiredFieldRules" lazy-rules ref="passwordRef">
        </PasswordFieldCustomInput>
        <q-btn color="primary" size="md" :label="$t('Update profile')" no-caps class="full-width q-my-xs"
          icon=" account_circle" :disable="state.loading || !profile.password" :loading="state.loading" type="submit">
          <template v-slot:loading>
            <q-spinner-hourglass class="on-left" />
            {{ t('Update profile') }}
          </template>
        </q-btn>
        <CustomBanner v-if="profileUpdatedSuccessfully" text="Profile has been successfully updated" success
          class="q-mt-lg">
        </CustomBanner>
        <CustomErrorBanner v-else-if="state.loadingError" :text="state.errorMessage || 'Error loading data'"
          :api-error="state.apiError" class="q-mt-lg">
        </CustomErrorBanner>
      </form>
    </template>
  </BaseWidget>
</template>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount, nextTick } from "vue";
import { useI18n } from "vue-i18n";

import { useBus } from "src/composables/useBus";
import { useAPI } from "src/composables/useAPI";
import { useFormUtils } from "src/composables/useFormUtils";

import { default as BaseWidget } from "src/components/Widgets/BaseWidget.vue";
import { default as PasswordFieldCustomInput } from "src/components/Forms/Fields/PasswordFieldCustomInput.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";

const { t } = useI18n();
const { api } = useAPI();
const { bus } = useBus();
const formUtils = useFormUtils();

const props = defineProps({
  autoFocus: {
    type: Boolean,
    required: false,
    default: false
  }
});

const state = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null
});

const profile = reactive({
  email: null,
  password: null
});

const emailRef = ref(null);
const passwordRef = ref(null);

const profileUpdatedSuccessfully = ref(false);

const validator = reactive({
  email: {
    hasErrors: false,
    message: null
  },
  password: {
    hasErrors: false,
    message: null
  }
});

const onResetForm = () => {
  validator.email.hasErrors = false;
  validator.email.message = null;
  validator.password.hasErrors = false;
  validator.password.message = null;
  emailRef.value?.resetValidation();
  passwordRef.value?.resetValidation();
}

const onGetProfile = () => {
  state.loading = true;
  state.loadingError = false;
  state.errorMessage = null;
  state.apiError = null;
  profile.email = null;
  profile.password = null;
  api.user.getProfile()
    .then((successResponse) => {
      profile.email = successResponse.data.data.email;
      state.loading = false;
      if (props.autoFocus) {
        nextTick(() => {
          passwordRef.value?.focus();
        });
      }
    })
    .catch((errorResponse) => {
      state.loadingError = true;
      if (errorResponse.isAPIError) {
        switch (errorResponse.response.status) {
          // TODO: invalid fields check
          case 401:
            state.apiError = errorResponse.customAPIErrorDetails;
            state.errorMessage = "Auth session expired, requesting new...";
            bus.emit("reAuthRequired", { emitter: "UpdateProfileForm.onGetProfile" });
            break;
          default:
            state.apiError = errorResponse.customAPIErrorDetails;
            state.errorMessage = "API Error: fatal error";
            break;
        }
      } else {
        state.errorMessage = `Uncaught exception: ${errorResponse}`;
        console.error(errorResponse);
      }
      state.loading = false;
      if (props.autoFocus) {
        nextTick(() => {
          passwordRef.value?.focus();
        });
      }
    });
}

const onValidateForm = () => {
  onResetForm();
  emailRef.value?.validate();
  passwordRef.value?.validate();
  if (!emailRef.value?.hasError && !passwordRef.value?.hasError) {
    onSubmitForm();
  } else {
    nextTick(() => {
      if (emailRef.value?.hasError) {
        emailRef.value?.focus();
      } else if (passwordRef.value?.hasError) {
        passwordRef.value?.focus();
      }
    });
  }
}

const onSubmitForm = () => {
  state.loading = true;
  state.loadingError = false;
  state.errorMessage = null;
  state.apiError = null;
  profileUpdatedSuccessfully.value = false;
  api.user
    .updateProfile(profile.email, profile.password)
    .then((successResponse) => {
      profileUpdatedSuccessfully.value = true;
      profile.email = successResponse.data.data.email;
      profile.password = null;
      state.loading = false;
      nextTick(() => {
        emailRef.value?.focus();
      });
    })
    .catch((errorResponse) => {
      state.loadingError = true;
      if (errorResponse.isAPIError) {
        switch (errorResponse.response.status) {
          // TODO: invalid fields check
          case 401:
            state.apiError = errorResponse.customAPIErrorDetails;
            state.errorMessage = "Auth session expired, requesting new...";
            bus.emit("reAuthRequired", { emitter: "UpdateProfileForm.onSubmitForm" });
            break;
          case 409: // email already exists
            validator.email.hasErrors = true;
            validator.email.message = "Email already used";
            state.errorMessage = "Error updating profile";
            nextTick(() => {
              emailRef.value?.focus();
            });
            break;
          default:
            state.apiError = errorResponse.customAPIErrorDetails;
            state.errorMessage = "API Error: fatal error";
            break;
        }
      } else {
        state.errorMessage = `Uncaught exception: ${errorResponse}`;
        console.error(errorResponse);
      }
      state.loading = false;
    });
}

onMounted(() => {
  onGetProfile();
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("UpdateProfileForm.onGetProfile")) {
      onGetProfile();
    }
    if (msg.to?.includes("UpdateProfileForm.onSubmitForm")) {
      onSubmitForm();
    }
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthSucess");
});

</script>