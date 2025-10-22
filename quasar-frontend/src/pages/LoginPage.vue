<template>
  <q-page>
    <q-card class="q-pa-xl" flat>
      <CSSAnimatedAudioEqualizer></CSSAnimatedAudioEqualizer>
      <LoginForm @success="onSuccessLogin"></LoginForm>
    </q-card>
  </q-page>
</template>

<script setup>
import { useRouter } from "vue-router";
import { default as LoginForm } from "src/components/Forms/LoginForm.vue";
import { default as CSSAnimatedAudioEqualizer } from "components/CSSAnimatedAudioEqualizer.vue";

const router = useRouter();

const onSuccessLogin = (apiResponseData) => {
  router.push({
    name: "dashboard",
  });
};

</script>