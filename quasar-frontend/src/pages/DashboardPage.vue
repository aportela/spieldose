<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="analytics" :label="t('Dashboard')" />
      </q-breadcrumbs>
      <q-tabs v-model="tab" inline-label no-caps dense class="text-pink-7 q-mb-md shadow-2">
        <q-tab v-for="tabElement in tabs" :key="tabElement.value" :name="tabElement.value" :icon="tabElement.icon"
          :label="t(tabElement.label)" />
      </q-tabs>
      <div class="row">
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockTop entity="tracks" :globalStats="tab == 'globalStats'"></DashboardBaseBlockTop>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockTop entity="artists" :globalStats="tab == 'globalStats'"></DashboardBaseBlockTop>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockTop entity="albums" :globalStats="tab == 'globalStats'"></DashboardBaseBlockTop>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockTop entity="genres" :globalStats="tab == 'globalStats'"></DashboardBaseBlockTop>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockRecently :played="true" :globalStats="tab == 'globalStats'"></DashboardBaseBlockRecently>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockRecently :added="true" :globalStats="tab == 'globalStats'"></DashboardBaseBlockRecently>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <DashboardBaseBlockChart :globalStats="tab == 'globalStats'"></DashboardBaseBlockChart>
        </div>
      </div>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { default as DashboardBaseBlockTop } from 'components/DashboardBaseBlockTop.vue';
import { default as DashboardBaseBlockRecently } from 'components/DashboardBaseBlockRecently.vue';
import { default as DashboardBaseBlockChart } from 'components/DashboardBaseBlockChart.vue';

const { t } = useI18n();

const tabs = ref([ { label: 'My stats', value: 'myStats', icon: 'person' }, { label: 'Global stats', value: 'globalStats', icon: 'public'}]);

const tab = ref(tabs.value[0].value)
</script>
