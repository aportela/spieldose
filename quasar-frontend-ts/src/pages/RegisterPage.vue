<template>
  <q-page>
    <q-card class="q-pa-md" flat>
      <CSSAnimatedAudioEqualizer />
      <RegisterForm />
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import { default as CSSAnimatedAudioEqualizer } from "src/components/Other/CSSAnimatedAudioEqualizer.vue";
import { default as RegisterForm } from "src/components/Forms/RegisterForm.vue";
</script>