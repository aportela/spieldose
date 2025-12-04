<template>
  <CustomBanner :text="text" error>
    <template v-slot:details v-if="serverEnvironment.isCurrentEnvironmentDevelopment && apiError">
      <APIErrorDetails class="q-mt-md" :api-error="apiError"></APIErrorDetails>
    </template>
  </CustomBanner>
</template>

<script setup lang="ts">
import { useServerEnvironmentStore } from "src/stores/serverEnvironment";

import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";
import { default as APIErrorDetails } from "src/components/APIErrorDetails.vue";

import type { APIErrorDetails as APIErrorDetailsInterface } from "src/types/api-error-details";

const serverEnvironment = useServerEnvironmentStore();

interface CustomErrorBannerProps {
  text: string,
  apiError?: APIErrorDetailsInterface | null;
};

defineProps<CustomErrorBannerProps>();
</script>