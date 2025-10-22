<template>
  <q-page>
    <q-card class="q-pa-xl" flat>
      <CSSAnimatedAudioEqualizer></CSSAnimatedAudioEqualizer>
      <SignInForm @success="onSuccessSignIn"></SignInForm>
    </q-card>
  </q-page>
</template>

<script setup>
import { useRouter } from "vue-router";
import { default as SignInForm } from "src/components/Forms/SignInForm.vue";
import { default as CSSAnimatedAudioEqualizer } from "components/CSSAnimatedAudioEqualizer.vue";

const router = useRouter();

const onSuccessSignIn = (apiResponseData) => {
  router.push({
    name: "dashboard",
  });
};

</script>