<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <q-breadcrumbs-el icon="person" :label="t('Browse paths')" />
    </q-breadcrumbs>
    <q-card-section>
      <CustomInputSearch :disable="loading" hint="Search paths with specified condition" placeholder="Text condition" v-model="filter" @update:modelValue="onFilterChanged"></CustomInputSearch>

      <q-btn-group v-if="! loading && directories && directories.length > 0" class="q-my-md">
        <q-btn size="sm" label="expand all" @click="onExpandAll" />
        <q-btn size="sm" label="collapse all" @click="onCollapseAll "/>
      </q-btn-group>

      <q-tree ref="treeRef" v-if="! loading" :nodes="directories" v-model:selected="selected" node-key="hash" label-key="name" children-key="children"
        no-transition @update:selected="onTreeNodeSelected" :default-expand-all="true" selected-color="pink" :filter="filter" :no-results-label="t('No matching paths found')" :no-nodes-label="t('No paths found')">
        <template v-slot:default-header="prop">
          <div v-if="prop.node.totalFiles > 0">
            <q-icon name="play_arrow" /> {{ prop.node.name }} <span v-if="prop.node.totalFiles > 0">({{
              prop.node.totalFiles }} total tracks)</span>
          </div>
          <span v-else>{{ prop.node.name }}</span>
        </template>
      </q-tree>
    </q-card-section>
  </q-card>
</template>

<script setup>

import { ref } from "vue";
import { api } from 'boot/axios'
import { useQuasar } from "quasar";
import { useI18n } from "vue-i18n";
import { pathActions } from "src/boot/spieldose";
import { default as CustomInputSearch } from "components/CustomInputSearch.vue";

const $q = useQuasar();
const { t } = useI18n();

const treeRef = ref(null);
const noPathsFound = ref(false);
const loading = ref(false);
const directories = ref([]);

const filter = ref('');
const selected = ref(null);

const isExpanded = ref(true);

function getTree() {
  noPathsFound.value = false;
  loading.value = true;
  api.path.getTree().then((success) => {
    directories.value = success.data.items;
    loading.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: t("API Error: error loading paths"),
      caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
    });
    loading.value = false;
  });
}


// https://stackoverflow.com/a/22222867
function findNode(hash, currentNode) {
  var i,
    currentChild,
    result;

  if (hash == currentNode.hash) {
    return currentNode;
  } else {

    // Use a for loop instead of forEach to avoid nested functions
    // Otherwise "return" will not work properly
    for (i = 0; i < currentNode.children.length; i += 1) {
      currentChild = currentNode.children[i];

      // Search in the current child
      result = findNode(hash, currentChild);

      // Return the result if the node has been found
      if (result !== false) {
        return result;
      }
    }

    // The node has not been found and we have no more options
    return false;
  }
}

function onTreeNodeSelected(nodeHash) {
  let node = findNode(nodeHash, directories.value[0]);
  if (node && node.id && node.totalFiles > 0) {
    pathActions.play(node.id).then((success) => {
    })
      .catch((error) => {
        switch (error.response.status) {
          default:
            $q.notify({
              type: "negative",
              message: t("API Error: error playing path"),
              caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
            });
            break;
        }
      });
  }
  return (true);
}

function onExpandAll() {
  treeRef.value.expandAll();
}

function onCollapseAll() {
  treeRef.value.collapseAll();
  isExpanded.value = false;
}

function onFilterChanged(v) {
  if (filter.value && ! isExpanded.value) {
    onExpandAll();
  }
}

getTree();

</script>
