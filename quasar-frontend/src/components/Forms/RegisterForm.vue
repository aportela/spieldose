<template>
  <form @submit.prevent.stop="onValidateForm" autocorrect="off" autocapitalize="off" autocomplete="off"
    spellcheck="false">
    <q-card-section class="text-center">
      <h4 class="q-mt-sm q-mb-md text-h4 text-weight-bolder">{{ t("Sign up now and take control.") }}</h4>
      <div class="text-grey-6">{{ t("The first track to your musical adventure begins here!") }}</div>
    </q-card-section>
    <q-card-section>
      <q-input dense outlined ref="emailRef" v-model="profile.email" type="email" name="email" :label="t('Email')"
        :disable="state.loading || signUpDenied" :autofocus="true" :rules="formUtils.requiredFieldRules" lazy-rules
        :error="validator.email.hasErrors" :error-message="validator.email.message ? t(validator.email.message) : ''">
        <template v-slot:prepend>
          <q-icon name="alternate_email" />
        </template>
      </q-input>
      <PasswordFieldCustomInput dense outlined ref="passwordRef" class="q-mt-md" v-model="profile.password"
        name="password" :label="t('Password')" :disable="state.loading || signUpDenied"
        :rules="formUtils.requiredFieldRules" lazy-rules :error="validator.password.hasErrors"
        :error-message="validator.password.message ? t(validator.password.message) : ''">
      </PasswordFieldCustomInput>
    </q-card-section>
    <q-card-section>
      <q-btn color="primary" size="md" :label="$t('Sign up')" no-caps class="full-width" icon="account_circle"
        :disable="state.loading || (!(profile.email && profile.password)) || signUpDenied" :loading="state.loading"
        type="submit">
        <template v-slot:loading>
          <q-spinner-hourglass class="on-left" />
          {{ t('Sign up') }}
        </template>
      </q-btn>
      <CustomBanner v-if="signUpDenied" text="New sign ups are not allowed on this system" error class="q-mt-lg">
      </CustomBanner>
      <CustomBanner v-else-if="userCreatedSuccessfully" text="Your account has been created" success class="q-mt-lg">
        <template v-slot:details>
          <q-btn size="sm" color="primary" class="float-right" :label="t('Sign in')" to="{ name: 'login'}"></q-btn>
        </template>
      </CustomBanner>
      <CustomErrorBanner v-else-if="state.loadingError && state.errorMessage" :text="state.errorMessage"
        :api-error="state.apiError" class="q-mt-lg">
      </CustomErrorBanner>
    </q-card-section>
    <q-card-section class="text-center q-pt-none">
      <div>
        {{ t("Already have an account ?") }}
        <router-link :to="{ name: 'login' }" class="main-app-text-link-hover">{{ t("Click here to sign in") }}
        </router-link>
      </div>
    </q-card-section>
    <q-separator class="q-mb-sm" />
    <q-card-section class="text-center q-py-none">
      <q-btn-group flat square>
        <DarkModeButton />
        <SwitchLanguageButton />
        <GitHubButton label="@2025 Spieldose" :href="GITHUB_PROJECT_URL" />
      </q-btn-group>
    </q-card-section>
  </form>
</template>

<script setup>

import { ref, reactive, nextTick, computed } from "vue";
import { uid } from "quasar";
import { useI18n } from "vue-i18n";

import { useAPI } from "src/composables/useAPI";
import { useFormUtils } from "src/composables/useFormUtils";
import { useInitialStateStore } from "src/stores/initialState";

import { default as DarkModeButton } from "src/components/Buttons/DarkModeButton.vue"
import { default as SwitchLanguageButton } from "src/components/Buttons/SwitchLanguageButton.vue"
import { default as GitHubButton } from "src/components/Buttons/GitHubButton.vue"
import { GITHUB_PROJECT_URL } from "src/constants"
import { default as PasswordFieldCustomInput } from "src/components/Forms/Fields/PasswordFieldCustomInput.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";

const emit = defineEmits(['success']);

const { t } = useI18n();

const { api } = useAPI();

const formUtils = useFormUtils();

const initialState = useInitialStateStore();

const signUpDenied = computed(() => initialState.isSignUpAllowed === false);

const state = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null
});

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

const profile = reactive(
  {
    email: null,
    password: null
  }
);

const emailRef = ref(null);
const passwordRef = ref(null);

const userCreatedSuccessfully = ref(false);

const onResetForm = () => {
  validator.email.hasErrors = false;
  validator.email.message = null;
  validator.password.hasErrors = false;
  validator.password.message = null;
  emailRef.value?.resetValidation();
  passwordRef.value?.resetValidation();
}

const onValidateForm = () => {
  onResetForm();
  emailRef.value?.validate();
  passwordRef.value?.validate();
  if (!(emailRef.value?.hasError || passwordRef.value?.hasError)) {
    onSubmitForm();
  }
}

const onSubmitForm = () => {
  state.loading = true;
  state.loadingError = false;
  state.errorMessage = null;
  state.apiError = null;
  userCreatedSuccessfully.value = false;
  api.auth
    .register(uid(), profile.email, profile.password)
    .then((successResponse) => {
      userCreatedSuccessfully.value = true;
      state.loading = false;
      emit("success", successResponse.data);
    })
    .catch((errorResponse) => {
      state.loadingError = true;
      if (errorResponse.isAPIError) {
        state.apiError = errorResponse.customAPIErrorDetails;
        switch (errorResponse.response.status) {
          case 400:
            if (
              errorResponse.response.data.invalidOrMissingParams.find(function (e) {
                return e === "email";
              })
            ) {
              state.loadingError = true;
              state.errorMessage = "API Error: missing email param";
              nextTick(() => {
                emailRef.value?.focus();
              });
            } else if (
              errorResponse.response.data.invalidOrMissingParams.find(function (e) {
                return e === "password";
              })
            ) {
              state.loadingError = true;
              state.errorMessage = "API Error: missing password param";
              nextTick(() => {
                passwordRef.value?.focus();
              });
            } else {
              state.loadingError = true;
              state.errorMessage = "API Error: invalid/missing param";
              nextTick(() => {
                emailRef.value?.focus();
              });
            }
            break;
          case 409:
            validator.email.hasErrors = true;
            validator.email.message = "Email already used";
            nextTick(() => {
              emailRef.value?.focus();
            });
            break;
          default:
            state.loadingError = true;
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

</script>