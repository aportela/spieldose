<template>
  <leftSidebar></leftSidebar>
  <div>Dashboard</div>

  <div class="row q-col-gutter-xl">
    <div class="col-4">
      <DashboardBaseBlock :icon="'format_list_numbered'" :title="'Top played tracks'"></DashboardBaseBlock>
    </div>
    <div class="col-4">
      <DashboardBaseBlock :icon="'format_list_numbered'" :title="'Top played albums'"></DashboardBaseBlock>
    </div>
    <div class="col-4">
      <DashboardBaseBlock :icon="'format_list_numbered'" :title="'Top played artists'"></DashboardBaseBlock>
    </div>
    <q-separator spaced />
  </div>
  <div class="row q-col-gutter-xl">
    <div class="col-4">
      <DashboardBaseBlock :icon="'format_list_numbered'" :title="'Top played genres'"></DashboardBaseBlock>
    </div>
    <div class="col-4">
      <DashboardBaseBlock :icon="'schedule'" :title="'Recently added'"></DashboardBaseBlock>
    </div>
    <div class="col-4">
      <DashboardBaseBlock :icon="'schedule'" :title="'Recently added'"></DashboardBaseBlock>
    </div>
    <q-separator spaced />
  </div>
  <DashboardBaseBlock :icon="'analytics'" :title="'Play statistics'">
    <template v-slot:body>
      <q-tabs v-model="tab" no-caps class="text-pink-7">
        <q-tab name="hour" label="By hour" />
        <q-tab name="weekday" label="By weekday" />
        <q-tab name="month" label="By month" />
        <q-tab name="year" label="By year" />
      </q-tabs>
      <div class="ct-chart"></div>
    </template>
  </DashboardBaseBlock>
</template>

<style lang="scss">
@import "~chartist/dist/index.css";
</style>

<script>
import { defineComponent } from 'vue'
import { default as leftSidebar } from 'components/AppLeftSidebar.vue';
import { default as dashboardBaseBlock } from 'components/DashboardBaseBlock.vue';
import { BarChart } from 'chartist';

export default defineComponent({
  name: 'DashboardPage',
  components: {
    'leftSidebar': leftSidebar,
    'DashboardBaseBlock': dashboardBaseBlock
  },
  setup() {
    const tab = 'weekday';
    return( { tab });
  },
  mounted() {
    new BarChart('.ct-chart', {
      labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
      series: [
        [12, 9, 7, 8, 5, 4, 3]
      ]
    }, {
      fullWidth: true,
      chartPadding: {
        right: 40
      }
    });
  }
})
</script>
