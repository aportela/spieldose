<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <q-breadcrumbs-el icon="person" :label="t('My profile')" />
    </q-breadcrumbs>
    <q-card-section style="height: 698px;">
      <form @submit.prevent.stop="onValidateForm" autocorrect="off" autocapitalize="off" autocomplete="off">
        <div class="row q-col-gutter-md">
          <div class="col-6">
            <q-input dense outlined ref="emailRef" v-model="email" type="email" name="email" :label="t('Email')"
              :disable="loading" :autofocus="true" :rules="requiredFieldRules" lazy-rules
              :error="remoteValidation.email.hasErrors" :errorMessage="remoteValidation.email.message">
              <template v-slot:prepend>
                <q-icon name="alternate_email" />
              </template>
            </q-input>
          </div>
          <div class="col-6">
            <q-input dense outlined ref="nameRef" v-model="name" type="text" name="name" :label="t('Name')"
              :disable="loading" :rules="requiredFieldRules" lazy-rules :error="remoteValidation.name.hasErrors"
              :errorMessage="remoteValidation.name.message">
              <template v-slot:prepend>
                <q-icon name="badge" />
              </template>
            </q-input>
          </div>
        </div>
        <div class="row q-col-gutter-md">
          <div class="col-6">
            <q-input dense outlined ref="passwordRef" v-model="password" name="password" type="password"
              :label="t('New password')" :disable="loading" :error="remoteValidation.password.hasErrors"
              :errorMessage="remoteValidation.password.message">
              <template v-slot:prepend>
                <q-icon name="key" />
              </template>
            </q-input>
          </div>
          <div class="col-6">
            <q-input dense outlined ref="confirmedPasswordRef" v-model="confirmedPassword" name="confirmedPassword"
              type="password" :label="t('Confirm password')" :disable="loading"
              :error="remoteValidation.confirmedPassword.hasErrors"
              :errorMessage="remoteValidation.confirmedPassword.message">
              <template v-slot:prepend>
                <q-icon name="key" />
              </template>
            </q-input>
          </div>
        </div>
        <div class="row q-mt-md">
          <div class="col-12">
            <q-btn color="dark" size="md" :label="$t('Save profile changes')" no-caps class="full-width" icon="save"
              :disable="loading || (!(email && name && passwordsMatch))" :loading="loading" type="submit">
              <template v-slot:loading>
                <q-spinner-hourglass class="on-left" />
                {{ t("Loading...") }}
              </template>
            </q-btn>
          </div>
        </div>
      </form>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, onMounted, computed, watch, inject, nextTick } from "vue";
import { useI18n } from "vue-i18n";
import { useQuasar } from "quasar";
import { api } from 'boot/axios';

const { t } = useI18n();
const $q = useQuasar();

const loading = ref(false);

const remoteValidation = ref({
  email: {
    hasErrors: false,
    message: null
  },
  name: {
    hasErrors: false,
    message: null
  },
  password: {
    hasErrors: false,
    message: null
  },
  confirmedPassword: {
    hasErrors: false,
    message: null
  }
});

const requiredFieldRules = [
  val => !!val || t('Field is required')
];

const email = ref(null);
const emailRef = ref(null);

const name = ref(null);
const nameRef = ref(null);

const password = ref(null);
const passwordRef = ref(null);

const confirmedPassword = ref(null);
const confirmedPasswordRef = ref(null);

const passwordsMatch = ref(true);

watch(password, (newValue) => {
  passwordsMatch.value = (newValue || "") == (confirmedPassword.value || "");
  if (passwordsMatch.value) {
    remoteValidation.value.password.hasErrors = false;
    remoteValidation.value.password.message = null;
    remoteValidation.value.confirmedPassword.hasErrors = false;
    remoteValidation.value.confirmedPassword.message = null;
  } else {
    remoteValidation.value.password.hasErrors = true;
    remoteValidation.value.password.message = remoteValidation.value.password.hasErrors ? t("Passwords don't match") : null;
  }
});

watch(confirmedPassword, (newValue) => {
  passwordsMatch.value = (newValue || "") == (password.value || "");
  if (passwordsMatch.value) {
    remoteValidation.value.password.hasErrors = false;
    remoteValidation.value.password.message = null;
    remoteValidation.value.confirmedPassword.hasErrors = false;
    remoteValidation.value.confirmedPassword.message = null;
  } else {
    remoteValidation.value.confirmedPassword.hasErrors = true;
    remoteValidation.value.confirmedPassword.message = remoteValidation.value.confirmedPassword.hasErrors ? t("Passwords don't match") : null;
  }
});

function refresh() {
  loading.value = true;
  api.user
    .getProfile()
    .then((success) => {
      email.value = success.data.email;
      name.value = success.data.name;
      password.value = null;
      confirmedPassword.value = null;
      loading.value = false;
      nextTick(() => {
        emailRef.value.focus();
      });
    })
    .catch((error) => {
      as
      $q.notify({
        type: "negative",
        message: t("API Error: error loading profile"),
        caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
      });
      loading.value = false;
    });
}

function onResetForm() {
  remoteValidation.value.email.hasErrors = false;
  remoteValidation.value.email.message = null;
  remoteValidation.value.name.hasErrors = false;
  remoteValidation.value.name.message = null;
  remoteValidation.value.password.hasErrors = false;
  remoteValidation.value.password.message = null;
  remoteValidation.value.confirmedPassword.hasErrors = false;
  remoteValidation.value.confirmedPassword.message = null;
  emailRef.value.resetValidation();
  passwordRef.value.resetValidation();
  confirmedPasswordRef.value.resetValidation();
}

function onValidateForm() {
  onResetForm();
  emailRef.value.validate();
  nameRef.value.validate();
  passwordRef.value.validate();
  nextTick(() => {
    if (!(emailRef.value.hasError || nameRef.value.hasError || passwordRef.value.hasError || confirmedPasswordRef.value.hasError)) {
      onSubmitForm();
    }
  });
}

function onSubmitForm() {
  loading.value = true;
  api.user
    .updateProfile(email.value, name.value, password.value)
    .then((success) => {
      email.value = success.data.email;
      name.value = success.data.name;
      password.value = null;
      confirmedPassword.value = null;
      $q.notify({
        type: "positive",
        message: t("Profile has been updated")
      });
      loading.value = false;
      nextTick(() => {
        emailRef.value.focus();
      });
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
          } else {
            $q.notify({
              type: "negative",
              message: t("API Error: invalid/missing param"),
            });
          }
          break;
        case 409:
          if (
            error.response.data.invalidOrMissingParams.find(function (e) {
              return e === "email";
            })
          ) {
            remoteValidation.value.email.hasErrors = true;
            remoteValidation.value.email.message = t("Email already used");
            nextTick(() => {
              emailRef.value.focus();
            });
          } else if (
            error.response.data.invalidOrMissingParams.find(function (e) {
              return e === "name";
            })
          ) {
            remoteValidation.value.name.hasErrors = true;
            remoteValidation.value.name.message = t("Name already used");
            nextTick(() => {
              nameRef.value.focus();
            });
          } else {
            $q.notify({
              type: "negative",
              message: t("API Error: fatal error"),
              caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
            });
          }
          break;
        case 410:
          remoteValidation.value.email.hasErrors = true;
          remoteValidation.value.email.message = t("Account has been deleted");
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

onMounted(() => {
  refresh();
});

</script>
