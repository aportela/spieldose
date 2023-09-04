<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="analytics" :label="t('Dashboard')" />
      </q-breadcrumbs>
      <div class="row">
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockTop entity="tracks"></DashboardBaseBlockTop>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockTop entity="artists"></DashboardBaseBlockTop>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockTop entity="albums"></DashboardBaseBlockTop>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockTop entity="genres"></DashboardBaseBlockTop>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockRecently :played="true"></DashboardBaseBlockRecently>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
          <DashboardBaseBlockRecently :added="true"></DashboardBaseBlockRecently>
        </div>
      </div>
      <DashboardBaseBlockChart></DashboardBaseBlockChart>
    </q-card>
  </q-page>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { default as DashboardBaseBlockTop } from 'components/DashboardBaseBlockTop.vue';
import { default as DashboardBaseBlockRecently } from 'components/DashboardBaseBlockRecently.vue';
import { default as DashboardBaseBlockChart } from 'components/DashboardBaseBlockChart.vue';

const { t } = useI18n();
</script>
