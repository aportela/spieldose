<template>
  <q-page class2="flex flex-center bg-grey-2">
    <div class="row items-center">
      <div class="col col-12 col-md-8 col-lg-6 col-xl-4 desktop-only justify-center q-pa-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="equilizer" viewBox="0 0 128 128">
          <g>
            <title>Audio Equalizer</title>
            <rect class="bar" transform="translate(0,0)" y="15"></rect>
            <rect class="bar" transform="translate(25,0)" y="15"></rect>
            <rect class="bar" transform="translate(50,0)" y="15"></rect>
            <rect class="bar" transform="translate(75,0)" y="15"></rect>
            <rect class="bar" transform="translate(100,0)" y="15"></rect>
          </g>
        </svg>
        <q-card class="q-pa-md my_card">
          <form @submit.prevent.stop="onValidateForm" autocorrect="off" autocapitalize="off" autocomplete="off"
            spellcheck="false" style="padding: 1.25rem;">
            <q-card-section class="text-center">
              <h3>{{ $t('Spieldose') }}</h3>
              <div class="text-grey-9 text-h5 text-weight-bold">{{ $t('Sign in') }}</div>
              <div class="text-grey-8">{{ $t('Sign in below to access your account') }}</div>
            </q-card-section>
            <q-card-section>
              <q-input dense outlined rounded ref="emailRef" v-model="email" type="email" name="email" :label="t('Email')"
                :disable="loading" :autofocus="true" :rules="requiredFieldRules" lazy-rules
                :error="remoteValidation.email.hasErrors" :errorMessage="remoteValidation.email.message">
                <template v-slot:prepend>
                  <q-icon name="alternate_email" />
                </template>
              </q-input>
              <q-input dense outlined rounded class="q-mt-md" ref="passwordRef" v-model="password" name="password" type="password"
                :label="t('Password')" :disable="loading" :rules="requiredFieldRules" lazy-rules
                :error="remoteValidation.password.hasErrors" :errorMessage="remoteValidation.password.message">
                <template v-slot:prepend>
                  <q-icon name="key" />
                </template>
              </q-input>
            </q-card-section>
            <q-card-section>
              <q-btn color="dark" size="md" :label="$t('Sign in')" no-caps class="full-width" icon="account_circle"
                :disable="loading || (!(email && password))" :loading="loading" type="submit">
                <template v-slot:loading>
                  <q-spinner-hourglass class="on-left" />
                  {{ t("Loading...") }}
                </template>
              </q-btn>
            </q-card-section>
            <q-card-section class="text-center q-pt-none">
              <div class="text-grey-8">
                {{ t("Don't have an account yet ?") }}
                <router-link :to="{ name: 'signUp' }">
                  <span class="text-dark text-weight-bold" style="text-decoration: none">{{
                    t("Click here to sign up") }}</span>
                </router-link>
              </div>
            </q-card-section>
          </form>
        </q-card>
      </div>
      <div class="col col-8 gt-lg container_tiles">
        <TitleAlbumImages></TitleAlbumImages>
      </div>
    </div>
  </q-page>
</template>


<style>
/* signin vu-meter effect */

.equilizer {
  height: 100px;
  width: 100%;
  transform: rotate(180deg);
}

.bar {
  fill: #dfd8d9;
  width: 18px;
  animation: equalize 1.25s steps(25, end) 0s infinite;
}

.bar:nth-child(1) {
  animation-duration: 1.9s;
}

.bar:nth-child(2) {
  animation-duration: 2s;
}

.bar:nth-child(3) {
  animation-duration: 2.3s;
}

.bar:nth-child(4) {
  animation-duration: 2.4s;
}

.bar:nth-child(5) {
  animation-duration: 2.1s;
}

@keyframes equalize {
  0% {
    height: 60px;
  }

  4% {
    height: 50px;
  }

  8% {
    height: 40px;
  }

  12% {
    height: 30px;
  }

  16% {
    height: 20px;
  }

  20% {
    height: 30px;
  }

  24% {
    height: 40px;
  }

  28% {
    height: 10px;
  }

  32% {
    height: 40px;
  }

  36% {
    height: 60px;
  }

  40% {
    height: 20px;
  }

  44% {
    height: 40px;
  }

  48% {
    height: 70px;
  }

  52% {
    height: 30px;
  }

  56% {
    height: 10px;
  }

  60% {
    height: 30px;
  }

  64% {
    height: 50px;
  }

  68% {
    height: 60px;
  }

  72% {
    height: 70px;
  }

  76% {
    height: 80px;
  }

  80% {
    height: 70px;
  }

  84% {
    height: 60px;
  }

  88% {
    height: 50px;
  }

  92% {
    height: 60px;
  }

  96% {
    height: 70px;
  }

  100% {
    height: 80px;
  }
}

/* signin vu-meter effect */
</style>

<script setup>

import { ref, nextTick } from "vue";
import { useQuasar } from "quasar";
import { useRouter } from "vue-router";
import { useI18n } from 'vue-i18n'
import { api } from 'boot/axios'

import { default as TitleAlbumImages } from "components/TileAlbumImages.vue";

const { t } = useI18n();

const $q = useQuasar();

const router = useRouter();

//const session = useSessionStore();

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
    .signIn(email.value, password.value)
    .then((success) => {
      // TODO
      //session.signIn();
      router.push({
        name: "index",
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
        case 404:
          remoteValidation.value.email.hasErrors = true;
          remoteValidation.value.email.message = t("Email not registered");
          nextTick(() => {
            emailRef.value.focus();
          });
          break;
        case 401:
          remoteValidation.value.password.hasErrors = true;
          remoteValidation.value.password.message = t("Invalid password");
          nextTick(() => {
            passwordRef.value.focus();
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
