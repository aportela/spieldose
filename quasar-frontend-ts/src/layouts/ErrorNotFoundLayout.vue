<template>
  <q-layout>
    <div class="row">
      <div class="col-12 col-lg-8 col-md-12 col-sm-12 slogan-background-image-cover flex flex-center">
        <div class="q-pa-xl text-center text-white">
          <h2 class="text-h2 text-weight-bolder q-mb-md">{{ t("Welcome to HomeDocs") }}</h2>
          <h4 class="text-h4 text-grey-4">{{ t("Where your documents find their place.") }}</h4>
        </div>
      </div>
      <div class="col-12 col-lg-4 flex" style="height: 100vh;">
        <div class="q-pa-xl text-center full-width">
          <div style="font-size: 30vh">404</div>
          <div class="text-h2" style="opacity:.4">{{ t('Oops. Nothing here...') }}</div>
          <div class="text-center">
            <q-btn color="primary" size="md" class="q-mt-xl full-width" no-caps :to="{ name: 'index' }"
              :label="t('Go Home')" />
          </div>
          <q-separator class="q-my-md" />
          <q-btn-group flat square>
            <DarkModeButton />
            <SwitchLanguageButton />
            <GitHubButton label="@2025 HomeDocs" :href="GITHUB_PROJECT_URL" />
          </q-btn-group>
        </div>
      </div>
    </div>
  </q-layout>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";

import { default as DarkModeButton } from "src/components/Buttons/DarkModeButton.vue"
import { default as SwitchLanguageButton } from "src/components/Buttons/SwitchLanguageButton.vue"
import { default as GitHubButton } from "src/components/Buttons/GitHubButton.vue"
import { GITHUB_PROJECT_URL } from "src/constants"

const { t } = useI18n();
</script>

<style lang="css" scoped>
.slogan-background-image-cover {
  /* image credits: https://www.pexels.com/photo/keyboard-and-mouse-on-beige-background-3184460/ */
  background-image: linear-gradient(135deg, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.8) 100%), url('/images/pexels-photo-3184460.jpg');
  background-size: cover;
  background-position: center;
  filter: grayscale(100%);
}
</style>