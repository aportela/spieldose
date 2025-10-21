<template>
  <CustomBanner :text="text" error>
    <template v-slot:details v-if="initialState.isDevEnvironment && apiError">
      <APIErrorDetails class="q-mt-md" :api-error="apiError"></APIErrorDetails>
    </template>
  </CustomBanner>
</template>

<script setup>

import { useInitialStateStore } from "src/stores/initialState";

import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";
import { default as APIErrorDetails } from "src/components/APIErrorDetails.vue";

const initialState = useInitialStateStore();

const props = defineProps({
  text: {
    type: String,
    required: true
  },
  apiError: {
    type: Object,
    required: false,
    default: null
  }
});

</script>
