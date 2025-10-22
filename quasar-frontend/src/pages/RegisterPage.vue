<template>
  <q-page>
    <q-card class="q-pa-xl" flat>
      <CSSAnimatedAudioEqualizer></CSSAnimatedAudioEqualizer>
      <RegisterForm @success="onSuccessRegister"></RegisterForm>
    </q-card>
  </q-page>
</template>

<script setup>
import { default as RegisterForm } from "src/components/Forms/RegisterForm.vue";
import { default as CSSAnimatedAudioEqualizer } from "components/CSSAnimatedAudioEqualizer.vue";

const onSuccessRegister = (apiResponseData) => {
};

</script>