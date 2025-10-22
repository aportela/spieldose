<template>
  <q-page>
    <q-card class="q-pa-xl" flat>
      <CSSAnimatedAudioEqualizer></CSSAnimatedAudioEqualizer>
      <SignUpForm @success="onSuccessSignUp"></SignUpForm>
    </q-card>
  </q-page>
</template>

<script setup>
import { default as SignUpForm } from "src/components/Forms/SignUpForm.vue";
import { default as CSSAnimatedAudioEqualizer } from "components/CSSAnimatedAudioEqualizer.vue";

const onSuccessSignUp = (apiResponseData) => {
};

</script>