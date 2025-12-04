<template>
  <q-page>
    <q-card class="q-pa-xl" flat>
      <LoginForm @success="onSuccessLogin" />
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import { useRouter } from "vue-router";
import { default as LoginForm } from "src/components/Forms/LoginForm.vue";

const router = useRouter();

const onSuccessLogin = () => {
  router.push(
    { name: "index" }
  ).catch((e) => {
    console.error(e);
  });;
};

</script>
