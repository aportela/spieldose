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
      GRAPH
    </template>
  </DashboardBaseBlock>
</template>

<script>
import { defineComponent } from 'vue'
import { default as leftSidebar } from 'components/AppLeftSidebar.vue';
import { default as dashboardBaseBlock } from 'components/DashboardBaseBlock.vue';


export default defineComponent({
  name: 'DashboardPage',
  components: {
    'leftSidebar': leftSidebar,
    'DashboardBaseBlock': dashboardBaseBlock
  }
})
</script>
