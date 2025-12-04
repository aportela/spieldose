<template>
  <form @submit.prevent.stop="onValidateForm" autocorrect="off" autocapitalize="off" autocomplete="off"
    spellcheck="false">
    <q-card-section class="text-center">
      <q-avatar square size="128px">
        <img src="icons/favicon-128x128.png" />
      </q-avatar>
      <slot name="slogan">
        <h4 class="q-mt-sm q-mb-md text-h4 text-weight-bolder">{{
          t(!!savedEmail ? "Glad to see you again!" : "Welcome aboard!")
        }}</h4>
        <div class="text-color-secondary">{{
          t(!!savedEmail ? "Let's get back to organizing." : "Let's start organizing.")
        }}
        </div>
      </slot>
    </q-card-section>
    <q-card-section>
      <q-input dense outlined ref="emailRef" v-model="profile.email" type="email" name="email" :label="t('Email')"
        :disable="state.ajaxRunning" :autofocus="!savedEmail" :rules="[requiredFieldRule]" lazy-rules
        :error="validator.email.hasErrors" :error-message="validator.email.message ? t(validator.email.message) : ''">
        <template v-slot:prepend>
          <q-icon name="alternate_email" />
        </template>
      </q-input>
      <PasswordFieldCustomInput dense outlined ref="passwordRef" class="q-mt-md" v-model="profile.password"
        name="password" :label="t('Password')" :disable="state.ajaxRunning" :autofocus="!!savedEmail"
        :rules="[requiredFieldRule]" lazy-rules :error="validator.password.hasErrors"
        :error-message="validator.password.message ? t(validator.password.message) : ''">
      </PasswordFieldCustomInput>
    </q-card-section>
    <q-card-section>
      <q-btn color="primary" size="md" :label="$t('Sign in')" no-caps class="full-width" icon="account_circle"
        :disable="state.ajaxRunning || (!(profile.email && profile.password))" :loading="state.ajaxRunning"
        type="submit">
        <template v-slot:loading>
          <q-spinner-hourglass class="on-left" />
          {{ t('Sign in') }}
        </template>
      </q-btn>
      <CustomErrorBanner v-if="state.ajaxErrors && state.ajaxErrorMessage" :text="state.ajaxErrorMessage"
        :api-error="state.ajaxAPIErrorDetails" class="q-mt-lg">
      </CustomErrorBanner>
    </q-card-section>
    <div v-if="showExtraBottom">
      <q-card-section class="text-center q-pt-none" v-if="serverEnvironment.isSignUpAllowed">
        <div>
          {{ t("Don't have an account yet ?") }}
          <router-link :to="{ name: 'register' }" class="main-app-text-link-hover">{{ t("Click here to sign up") }}
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
    </div>
  </form>
</template>

<script setup lang="ts">

import { ref, reactive, nextTick } from "vue";
import { useI18n } from "vue-i18n";
import { QInput } from "quasar";

import { api } from "src/composables/api";
import { useFormUtils } from "src/composables/useFormUtils";
import { useServerEnvironmentStore } from "src/stores/serverEnvironment";
import { useSessionStore } from "src/stores/session";
import { email as localStorageEmail } from "src/composables/localStorage";
import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajax-state";
import { type AuthValidator as AuthValidatorInterface, defaultAuthValidator } from "src/types/auth-validator";
import { type AuthFields as AuthFieldsInterface } from "src/types/auth-fields";
import { type LoginResponse } from "src/types/api-responses";
import { default as DarkModeButton } from "src/components/Buttons/DarkModeButton.vue"
import { default as SwitchLanguageButton } from "src/components/Buttons/SwitchLanguageButton.vue"
import { default as GitHubButton } from "src/components/Buttons/GitHubButton.vue"
import { GITHUB_PROJECT_URL } from "src/constants"
import { default as PasswordFieldCustomInput } from "src/components/Forms/Fields/PasswordFieldCustomInput.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";

interface LoginFormProps {
  showExtraBottom?: boolean;
};

withDefaults(defineProps<LoginFormProps>(), {
  showExtraBottom: true,
});

const emit = defineEmits(['success']);

const { t } = useI18n();

const { requiredFieldRule } = useFormUtils();

const serverEnvironment = useServerEnvironmentStore();

const sessionStore = useSessionStore();

const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

const validator = reactive<AuthValidatorInterface>({ ...defaultAuthValidator });

const savedEmail = localStorageEmail.get();

const profile = reactive<AuthFieldsInterface>(
  {
    email: savedEmail || "",
    password: ""
  }
);

const emailRef = ref<QInput | null>(null);
const passwordRef = ref<QInput | null>(null);

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
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.auth
      .login(profile.email, profile.password)
      .then((successResponse: LoginResponse) => {
        sessionStore.setAccessToken(successResponse.data.accessToken);
        localStorageEmail.set(profile.email);
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
            case 404:
              validator.email.hasErrors = true;
              validator.email.message = "Email not registered";
              nextTick()
                .then(() => {
                  emailRef.value?.focus();
                }).catch((e) => {
                  console.error(e);
                });
              break;
            case 401:
              validator.password.hasErrors = true;
              validator.password.message = "Invalid password";
              nextTick()
                .then(() => {
                  passwordRef.value?.focus();
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