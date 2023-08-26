<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="person" label="Browse paths" />
      </q-breadcrumbs>

      <q-card-section v-if="directories">

        <q-input v-model="pathName" rounded clearable type="search" outlined dense placeholder="Text condition"
          hint="Search paths with name" :loading="loading" :disable="loading" @keydown.enter.prevent="search"
          @clear="noPathsFound = false" :error="noPathsFound" :errorMessage="'No paths found with specified condition'">
          <template v-slot:prepend>
            <q-icon name="filter_alt" />
          </template>
          <template v-slot:append>
            <q-icon name="search" class="cursor-pointer" @click="search" />
          </template>
        </q-input>
        <div class="q-pa-lg flex flex-center" v-if="totalPages > 1">
          <q-pagination v-model="currentPageIndex" color="dark" :max="totalPages" :max-pages="5" boundary-numbers
            direction-links boundary-links @update:model-value="onPaginationChanged" :disable="loading" />
        </div>
        <div>
          <q-tree :nodes="directories" :label-key="path" no-transition node-key="label" no-connectors>
            <template v-slot:default-header="prop">
              <div v-if="prop.node.totalFiles">
                {{ prop.node.path }} ({{ prop.node.totalFiles}} total tracks)
              </div>
              <span v-else>{{ prop.node.path }} {{ prop.node.totalFiles}}</span>
            </template>
          </q-tree>
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup>

import { ref } from "vue";
import { api } from 'boot/axios'

const pathName = ref(null);
const noPathsFound = ref(false);
const loading = ref(false);
const directories = ref([]);

const totalPages = ref(10);
const currentPageIndex = ref(1);

const pathTree = ref([]);

function getTree() {
  noPathsFound.value = false;
  loading.value = true;
  api.path.getTree(currentPageIndex.value, 32, { path: pathName.value }).then((success) => {
    directories.value = success.data.data.items;
    totalPages.value = success.data.data.pager.totalPages;
    if (pathName.value && success.data.data.pager.totalResults < 1) {
      noPathsFound.value = true;
    }
    loading.value = false;
  }).catch((error) => {
    loading.value = false;
  });
}


function onPaginationChanged(pageIndex) {
  currentPageIndex.value = pageIndex;
  getTree();
}

getTree();

</script>
