<template>
  <q-page>
    <div class="my-profile-header-container">
      <div class="my-profile-header-background-image-cover flex flex-center q-mx-auto">
        <q-avatar icon="edit" size="48px" class="bg-dark text-white my-profile-header-top-right-icon" />
        <h2 class="text-h2 text-white text-weight-bolder q-my-none">{{ t("My profile") }}</h2>
      </div>
      <div class="text-center">
        <q-avatar icon="account_circle" size="160px" class="bg-grey-4 q-mx-auto my-profile-header-center-icon" />
      </div>
    </div>
    <div class="row q-col-gutter-sm">
      <div class="col-lg-4 col-xl-4 col-12 flex">
        <UpdateProfileForm></UpdateProfileForm>
      </div>
      <div class="col-lg-4 col-xl-4 col-12 flex">
        <SidebarSpectrumAnalyzerSettings />
      </div>
      <div class="col-lg-4 col-xl-4 col-12 flex">
        <q-card class="full-width">
          <q-item class="theme-default-q-card-section-header">
            Mini Analog Vumeter Settings
          </q-item>
          <q-separator />
          <q-card-section>
            <p>
              <q-toggle v-model="showMiniAnalogVumeter" label="visible"
                @update:model-value="onChangeShowMiniAnalogVumeter" />
            </p>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">

import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { default as UpdateProfileForm } from "src/components/Forms/UpdateProfileForm.vue";
import { useSidebarAnalogVumeterSettingsStore } from "src/stores/sidebarAnalogVumeterSettings";
import { default as SidebarSpectrumAnalyzerSettings } from "src/components/Widgets/Settings/SidebarSpectrumAnalyzerSettings.vue

const { t } = useI18n();


const sidebarAnalogVumeterSettingsStore = useSidebarAnalogVumeterSettingsStore();

const showMiniAnalogVumeter = ref(sidebarAnalogVumeterSettingsStore.visible);


watch(() => sidebarAnalogVumeterSettingsStore.visible, (newValue) => {
  showMiniAnalogVumeter.value = newValue;
});



const onChangeShowMiniAnalogVumeter = (visible: boolean) => {
  sidebarAnalogVumeterSettingsStore.setVisibility(visible);
};

</script>

<style lang="css" scoped>
.my-profile-header-container {
  height: 350px;
  margin-bottom: 1em;
}

.my-profile-header-background-image-cover {
  width: 100%;
  height: 260px;
  /* image credits: https://www.pexels.com/photo/keyboard-and-mouse-on-beige-background-3184460/ */
  background-image: linear-gradient(135deg, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.8) 100%), url('/images/pexels-photo-3184460.jpg');
  background-size: cover;
  background-position: bottom;
  filter: grayscale(100%);
  border-radius: 16px;
}

.my-profile-header-top-right-icon {
  position: absolute;
  top: 16px;
  right: 16px;
  color: blue;
}

.my-profile-header-center-icon {
  position: relative;
  top: -80px;
}
</style>