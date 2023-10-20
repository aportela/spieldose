<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <q-breadcrumbs-el icon="person" :label="t('My profile')" />
    </q-breadcrumbs>
    <q-card-section style="height: 698px;">

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
          <q-input dense outlined ref="nameRef" v-model="name" type="text" name="name" :label="t('Name')" :disable="loading"
          :rules="requiredFieldRules" lazy-rules :error="remoteValidation.name.hasErrors"
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
          :label="t('Password')" :disable="loading" :rules="requiredFieldRules" lazy-rules
          :error="remoteValidation.password.hasErrors" :errorMessage="remoteValidation.password.message">
          <template v-slot:prepend>
            <q-icon name="key" />
          </template>
        </q-input>
        </div>
        <div class="col-6">
          <q-input dense outlined ref="passwordRef" v-model="password" name="password" type="password"
          :label="t('Password')" :disable="loading" :rules="requiredFieldRules" lazy-rules
          :error="remoteValidation.password.hasErrors" :errorMessage="remoteValidation.password.message">
          <template v-slot:prepend>
            <q-icon name="key" />
          </template>
        </q-input>
        </div>
      </div>



    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, onMounted, computed, watch, inject, nextTick } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { useQuasar } from "quasar";
import { api } from 'boot/axios';

const { t } = useI18n();
const $q = useQuasar();

const route = useRoute();
const router = useRouter();

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

</script>
