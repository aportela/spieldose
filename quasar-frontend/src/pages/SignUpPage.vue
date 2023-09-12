<template>
  <q-card class="q-pa-md my_card">
    <form @submit.prevent.stop="onSubmitForm" autocorrect="off" autocapitalize="off" autocomplete="off"
      spellcheck="false">
      <q-card-section class="text-center">
        <h3>{{ $t('Spieldose') }}</h3>
        <div class="text-grey-8">{{ $t('Sign up below to create your account') }}</div>
      </q-card-section>
      <q-card-section>
        <q-input dense outlined ref="emailRef" v-model="email" type="email" name="email" :label="t('Email')"
          :disable="loading" :autofocus="true" :rules="requiredFieldRules" lazy-rules
          :error="remoteValidation.email.hasErrors" :errorMessage="remoteValidation.email.message">
          <template v-slot:prepend>
            <q-icon name="alternate_email" />
          </template>
        </q-input>
        <q-input dense outlined class="q-mt-md" ref="passwordRef" v-model="password" name="password" type="password"
          :label="t('Password')" :disable="loading" :rules="requiredFieldRules" lazy-rules
          :error="remoteValidation.password.hasErrors" :errorMessage="remoteValidation.password.message">
          <template v-slot:prepend>
            <q-icon name="key" />
          </template>
        </q-input>
      </q-card-section>
      <q-card-section>
        <q-btn color="dark" size="md" :label="$t('Sign up')" no-caps class="full-width" icon="account_circle"
          :disable="loading || (!(email && password))" :loading="loading" type="submit">
          <template v-slot:loading>
            <q-spinner-hourglass class="on-left" />
            {{ t("Loading...") }}
          </template>
        </q-btn>
      </q-card-section>
      <q-card-section class="text-center q-pt-none">
        <div class="text-grey-8">
          {{ t("Already have an account ?") }}
          <router-link :to="{ name: 'signIn' }">
            <span class="text-dark text-weight-bold" style="text-decoration: none">{{
              t("Click here to sign in") }}</span>
          </router-link>
        </div>
      </q-card-section>
    </form>
  </q-card>
</template>

<script setup>

import { ref } from "vue";
import { uid, useQuasar } from "quasar";
import { useRouter } from "vue-router";
import { useI18n } from 'vue-i18n';
import { api } from 'boot/axios';

const { t } = useI18n();

const $q = useQuasar();

const router = useRouter();

const loading = ref(false);

const remoteValidation = ref({
  email: {
    hasErrors: false,
    message: null
  },
  password: {
    hasErrors: false,
    message: null
  }
});

const requiredFieldRules = [
  val => !!val || t('Field is required')
];

const email = ref(null);
const emailRef = ref(null);

const password = ref(null);
const passwordRef = ref(null);

function onResetForm() {
  remoteValidation.value.email.hasErrors = false;
  remoteValidation.value.email.message = null;
  remoteValidation.value.password.hasErrors = false;
  remoteValidation.value.password.message = null;
  emailRef.value.resetValidation();
  passwordRef.value.resetValidation();
}

function onValidateForm() {
  onResetForm();
  emailRef.value.validate();
  passwordRef.value.validate();
  nextTick(() => {
    if (!(emailRef.value.hasError || passwordRef.value.hasError)) {
      onSubmitForm();
    }
  });
}

function onSubmitForm() {
  loading.value = true;
  api.user
    .signUp(uid(), email.value, password.value)
    .then((success) => {
      $q.notify({
        type: "positive",
        message: t("Your account has been created"),
        actions: [
          {
            label: t("Sign in"), color: 'white', handler: () => {
              router.push({
                name: "signIn",
              });
            }
          }
        ]
      });
      loading.value = false;
    })
    .catch((error) => {
      loading.value = false;
      switch (error.response.status) {
        case 400:
          if (
            error.response.data.invalidOrMissingParams.find(function (e) {
              return e === "email";
            })
          ) {
            $q.notify({
              type: "negative",
              message: t("API Error: missing email param"),
            });
            emailRef.value.focus();
          } else if (
            error.response.data.invalidOrMissingParams.find(function (e) {
              return e === "password";
            })
          ) {
            $q.notify({
              type: "negative",
              message: t("API Error: missing password param"),
            });
            passwordRef.value.focus();
          } else {
            $q.notify({
              type: "negative",
              message: t("API Error: invalid/missing param"),
            });
          }
          break;
        case 409:
          remoteValidation.value.email.hasErrors = true;
          remoteValidation.value.email.message = t("Email already used");
          nextTick(() => {
            emailRef.value.focus();
          });
          break;
        default:
          $q.notify({
            type: "negative",
            message: t("API Error: fatal error"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
    });
}

</script>
