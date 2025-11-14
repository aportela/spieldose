<template>
  <q-layout>
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-3">
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
            <GitHubButton label="@2025 Spieldose" :href="GITHUB_PROJECT_URL" />
          </q-btn-group>
        </div>
      </div>
      <div class="col-md-6 col-lg-8 col-xl-9 gt-sm">
        <TileAlbumImages></TileAlbumImages>
      </div>
    </div>
  </q-layout>
</template>

<script setup>

import { useI18n } from "vue-i18n";

import { default as TileAlbumImages } from "components/TileAlbumImages.vue";
import { default as DarkModeButton } from "src/components/Buttons/DarkModeButton.vue"
import { default as SwitchLanguageButton } from "src/components/Buttons/SwitchLanguageButton.vue"
import { default as GitHubButton } from "src/components/Buttons/GitHubButton.vue"
import { GITHUB_PROJECT_URL } from "src/constants"

const { t } = useI18n();

</script>