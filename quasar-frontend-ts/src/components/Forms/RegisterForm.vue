<template>
  <form @submit.prevent.stop="onValidateForm" autocorrect="off" autocapitalize="off" autocomplete="off"
    spellcheck="false">
    <q-card-section class="text-center">
      <q-avatar square size="128px">
        <img src="icons/favicon-128x128.png" />
      </q-avatar>
      <h4 class="q-mt-sm q-mb-md text-h4 text-weight-bolder">{{ t("Sign up now and take control.") }}</h4>
      <div class="text-grey-6">{{ t("The first step to a more organized you starts here!") }}</div>
    </q-card-section>
    <q-card-section>
      <q-input dense outlined ref="emailRef" v-model="profile.email" type="email" name="email" :label="t('Email')"
        :disable="state.ajaxRunning || signUpDenied" :autofocus="true" :rules="[requiredFieldRule]" lazy-rules
        :error="validator.email.hasErrors" :error-message="validator.email.message ? t(validator.email.message) : ''">
        <template v-slot:prepend>
          <q-icon name="alternate_email" />
        </template>
      </q-input>
      <PasswordFieldCustomInput dense outlined ref="passwordRef" class="q-mt-md" v-model="profile.password"
        name="password" :label="t('Password')" :disable="state.ajaxRunning || signUpDenied" :rules="[requiredFieldRule]"
        lazy-rules :error="validator.password.hasErrors"
        :error-message="validator.password.message ? t(validator.password.message) : ''">
      </PasswordFieldCustomInput>
    </q-card-section>
    <q-card-section>
      <q-btn color="primary" size="md" :label="$t('Sign up')" no-caps class="full-width" icon="account_circle"
        :disable="state.ajaxRunning || (!(profile.email && profile.password)) || signUpDenied"
        :loading="state.ajaxRunning" type="submit">
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
      <CustomErrorBanner v-else-if="state.ajaxErrors && state.ajaxErrorMessage" :text="state.ajaxErrorMessage"
        :api-error="state.ajaxAPIErrorDetails" class="q-mt-lg">
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
        <GitHubButton label="@2025 HomeDocs" :href="GITHUB_PROJECT_URL" />
      </q-btn-group>
    </q-card-section>
  </form>
</template>

<script setup lang="ts">

import { ref, reactive, nextTick, computed } from "vue";
import { uid } from "quasar";
import { useI18n } from "vue-i18n";
import { QInput } from "quasar";

import { api } from "src/composables/api";
import { useFormUtils } from "src/composables/useFormUtils";
import { useServerEnvironmentStore } from "src/stores/serverEnvironment";
import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajax-state";
import { type AuthValidator as AuthValidatorInterface, defaultAuthValidator } from "src/types/auth-validator";
import { type AuthFields as AuthFieldsInterface } from "src/types/auth-fields";
import { type RegisterResponse } from "src/types/api-responses";

import { default as DarkModeButton } from "src/components/Buttons/DarkModeButton.vue"
import { default as SwitchLanguageButton } from "src/components/Buttons/SwitchLanguageButton.vue"
import { default as GitHubButton } from "src/components/Buttons/GitHubButton.vue"
import { GITHUB_PROJECT_URL } from "src/constants"
import { default as PasswordFieldCustomInput } from "src/components/Forms/Fields/PasswordFieldCustomInput.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";

const emit = defineEmits(['success']);

const { t } = useI18n();

const { requiredFieldRule } = useFormUtils();

const serverEnvironment = useServerEnvironmentStore();

const signUpDenied = computed(() => serverEnvironment.isSignUpAllowed === false);

const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

const validator = reactive<AuthValidatorInterface>({ ...defaultAuthValidator });

const profile = reactive<AuthFieldsInterface>(
  {
    email: "",
    password: ""
  }
);

const emailRef = ref<QInput | null>(null);
const passwordRef = ref<QInput | null>(null);

const userCreatedSuccessfully = ref(false);

const onResetForm = () => {
  validator.email.hasErrors = false;
  validator.email.message = null;
  validator.password.hasErrors = false;
  validator.password.message = null;
  emailRef.value?.resetValidation();
  passwordRef.value?.resetValidation();
}

const onValidateForm = async () => {
  onResetForm();
  try {
    await emailRef.value?.validate();
    await passwordRef.value?.validate();
    if (emailRef.value?.hasError) {
      emailRef.value?.focus();
    } else if (passwordRef.value?.hasError) {
      passwordRef.value?.focus();
    } else {
      onSubmitForm();
    }
  } catch (error) {
    console.error('Validation error', error);
  }
};

const onSubmitForm = () => {
  if (profile.email && profile.password) {
    userCreatedSuccessfully.value = false;
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.auth
      .register(uid(), profile.email, profile.password)
      .then((successResponse: RegisterResponse) => {
        userCreatedSuccessfully.value = true;
        emit("success", successResponse.data);
      })
      .catch((errorResponse) => {
        state.ajaxErrors = true;
        if (errorResponse.isAPIError) {
          state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
          switch (errorResponse.response.status) {
            case 400:
              if (
                errorResponse.response.data.invalidOrMissingParams.find(function (e: string) {
                  return e === "email";
                })
              ) {
                state.ajaxErrorMessage = "API Error: missing email param";
                nextTick()
                  .then(() => {
                    emailRef.value?.focus();
                  }).catch((e) => {
                    console.error(e);
                  });
              } else if (
                errorResponse.response.data.invalidOrMissingParams.find(function (e: string) {
                  return e === "password";
                })
              ) {
                state.ajaxErrorMessage = "API Error: missing password param";
                nextTick()
                  .then(() => {
                    passwordRef.value?.focus();
                  }).catch((e) => {
                    console.error(e);
                  });
              } else {
                state.ajaxErrorMessage = "API Error: invalid/missing param";
                nextTick()
                  .then(() => {
                    emailRef.value?.focus();
                  }).catch((e) => {
                    console.error(e);
                  });
              }
              break;
            case 409:
              validator.email.hasErrors = true;
              validator.email.message = "Email already used";
              nextTick()
                .then(() => {
                  emailRef.value?.focus();
                }).catch((e) => {
                  console.error(e);
                });
              break;
            default:
              state.ajaxErrorMessage = "API Error: fatal error";
              break;
          }
        } else {
          state.ajaxErrorMessage = `Uncaught exception: ${errorResponse}`;
          console.error(errorResponse);
        }
      }).finally(() => {
        state.ajaxRunning = false;
      });
  } else {
    // TODO
    console.error("Missing email|password values");
  }
}

</script>