<template>
  <BaseWidget title="Personal information" caption="Update your data" icon="contact_mail">
    <template v-slot:content>
      <form @submit.prevent.stop="onValidateForm" autocorrect="off" autocapitalize="off" autocomplete="off"
        spellcheck="false">
        <q-input class="q-my-md" dense outlined v-model="profile.email" type="email" name="email" :label="t('Email')"
          :disable="state.ajaxRunning" :error="validator.email.hasErrors"
          :error-message="validator.email.message ? t(validator.email.message) : ''" :rules="[requiredFieldRule]"
          lazy-rules ref="emailRef">
          <template v-slot:prepend>
            <q-icon name="alternate_email" />
          </template>
        </q-input>
        <PasswordFieldCustomInput class="q-my-md" dense outlined v-model="profile.password" name="password"
          :label="t('New password')" :error="validator.password.hasErrors"
          :error-message="validator.password.message ? t(validator.password.message) : ''" :disable="state.ajaxRunning"
          :rules="[requiredFieldRule]" lazy-rules ref="passwordRef">
        </PasswordFieldCustomInput>
        <q-btn color="primary" size="md" :label="$t('Update profile')" no-caps class="full-width q-my-xs"
          icon=" account_circle" :disable="state.ajaxRunning || !profile.password" :loading="state.ajaxRunning"
          type="submit">
          <template v-slot:loading>
            <q-spinner-hourglass class="on-left" />
            {{ t('Update profile') }}
          </template>
        </q-btn>
        <CustomBanner v-if="profileUpdatedSuccessfully" text="Profile has been successfully updated" success
          class="q-mt-lg">
        </CustomBanner>
        <CustomErrorBanner v-else-if="state.ajaxErrors && state.ajaxErrorMessage" :text="state.ajaxErrorMessage"
          :api-error="state.ajaxAPIErrorDetails" class="q-mt-lg">
        </CustomErrorBanner>
      </form>
    </template>
  </BaseWidget>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, onBeforeUnmount, nextTick } from "vue";
import { useI18n } from "vue-i18n";
import { QInput } from "quasar";

import { bus } from "src/composables/bus";
import { api } from "src/composables/api";
import { useFormUtils } from "src/composables/useFormUtils";
import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajax-state";
import { type AuthValidator as AuthValidatorInterface, defaultAuthValidator } from "src/types/auth-validator";
import { type AuthFields as AuthFieldsInterface } from "src/types/auth-fields";
import { type GetProfileResponse, type SetProfileResponse } from "src/types/api-responses";

import { default as BaseWidget } from "src/components/Widgets/BaseWidget.vue";
import { default as PasswordFieldCustomInput } from "src/components/Forms/Fields/PasswordFieldCustomInput.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";

const { t } = useI18n();
const { requiredFieldRule } = useFormUtils();

interface UpdateProfileFormProps {
  autoFocus?: boolean;
};

const props = withDefaults(defineProps<UpdateProfileFormProps>(), {
  autoFocus: false
});

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

const profileUpdatedSuccessfully = ref<boolean>(false);

const onResetForm = () => {
  validator.email.hasErrors = false;
  validator.email.message = null;
  validator.password.hasErrors = false;
  validator.password.message = null;
  emailRef.value?.resetValidation();
  passwordRef.value?.resetValidation();
}

const onGetProfile = () => {
  profile.email = "";
  profile.password = "";
  Object.assign(state, defaultAjaxState);
  state.ajaxRunning = true;
  api.user.getProfile()
    .then((successResponse: GetProfileResponse) => {
      profile.email = successResponse.data.user.email;
      if (props.autoFocus) {
        nextTick()
          .then(() => {
            passwordRef.value?.focus();
          }).catch((e) => {
            console.error(e);
          });
      }
    })
    .catch((errorResponse) => {
      state.ajaxErrors = true;
      if (errorResponse.isAPIError) {
        state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
        switch (errorResponse.response.status) {
          // TODO: invalid fields check
          case 401:
            state.ajaxErrors = false;
            bus.emit("reAuthRequired", { emitter: "UpdateProfileForm.onGetProfile" });
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
      if (props.autoFocus) {
        nextTick()
          .then(() => {
            passwordRef.value?.focus();
          }).catch((e) => {
            console.error(e);
          });
      }
    });
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
    profileUpdatedSuccessfully.value = false;
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.user
      .setProfile(profile.email, profile.password)
      .then((successResponse: SetProfileResponse) => {
        profileUpdatedSuccessfully.value = true;
        profile.email = successResponse.data.user.email;
        profile.password = "";
        nextTick()
          .then(() => {
            emailRef.value?.focus();
          }).catch((e) => {
            console.error(e);
          });
      })
      .catch((errorResponse) => {
        state.ajaxErrors = true;
        if (errorResponse.isAPIError) {
          state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
          switch (errorResponse.response.status) {
            // TODO: invalid fields check
            case 401:
              state.ajaxErrors = false;
              bus.emit("reAuthRequired", { emitter: "UpdateProfileForm.onSubmitForm" });
              break;
            case 409: // email already exists
              validator.email.hasErrors = true;
              validator.email.message = "Email already used";
              state.ajaxErrorMessage = "Error updating profile";
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