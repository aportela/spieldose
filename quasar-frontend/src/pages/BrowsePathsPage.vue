<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <q-breadcrumbs-el icon="person" :label="t('Browse paths')" />
    </q-breadcrumbs>
    <q-card-section>
      <CustomInputSearch :disable="loading" hint="Search paths with specified condition" placeholder="Text condition"
        v-model="filter" @update:modelValue="onFilterChanged"></CustomInputSearch>

      <q-btn-group v-if="!loading && directories && directories.length > 0" class="q-my-md">
        <q-btn size="sm" label="expand all" @click="onExpandAll" />
        <q-btn size="sm" label="collapse all" @click="onCollapseAll" />
      </q-btn-group>

      <q-tree ref="treeRef" v-if="!loading" :nodes="directories" v-model:selected="selected" node-key="hash"
        label-key="name" children-key="children" no-transition @update:selected="onTreeNodeSelected"
        :default-expand-all="true" selected-color="pink" :filter="filter"
        :no-results-label="t('No matching paths found')" :no-nodes-label="t('No paths found')">
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

import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useAPI } from "src/composables/useAPI";
import { uid, useQuasar } from "quasar";
import { useI18n } from "vue-i18n";
import { pathActions } from "src/boot/spieldose";
import { default as CustomInputSearch } from "components/CustomInputSearch.vue";

const $q = useQuasar();
const { t } = useI18n();

const route = useRoute();

const { api } = useAPI();

const skipCount = ref(false);

const warningNoItems = ref(false);

const treeRef = ref(null);
const noPathsFound = ref(false);
const loading = ref(false);
const directories = ref([]);

const paths = ref([]);

const filter = ref('');
const selected = ref(null);

const isExpanded = ref(true);

const sortField = ref(route.query.sortField == "totalTracks" ? "totalTracks" : "name");
const sortOrder = ref(route.query.sortOrder == "DESC" ? "DESC" : "ASC");

const totalPages = ref(0);
const totalResults = ref(0);
const currentPageIndex = ref(parseInt(route.query.page || 1));

const lastChangesTimestamp = ref(0);

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

function browse() {
  warningNoItems.value = false;
  loading.value = true;
  api.browse.path({}, currentPageIndex.value, 32, sortField.value, sortOrder.value, skipCount.value).then((success) => {
    // create unique id
    paths.value = success.data.data.items.map((item) => { item._id = uid(); return (item); });
    if (success.data.data.pager) {
      totalPages.value = success.data.data.pager.totalPages;
      totalResults.value = success.data.data.pager.totalResults;
      warningNoItems.value = success.data.data.pager.totalResults < 1;
      skipCount.value = true;
    }
    loading.value = false;
    lastChangesTimestamp.value = Date.now();
    const arbol = {};
    paths.value.forEach(item => {
      const partes = item.name.split('\\').filter(Boolean);
      let nodoActual = arbol;
      partes.forEach((parte, index) => {
        // Si no existe la parte, la creamos
        if (!nodoActual[parte]) {
          nodoActual[parte] = { _id: uid(), name: parte, children: {} };
        }
        // Mover al siguiente nivel del árbol
        nodoActual = nodoActual[parte].children;
        // Si estamos en la última parte (el álbum), asignamos los datos
        if (index === partes.length - 1) {
          nodoActual['id'] = item.id;
          nodoActual['totalFiles'] = item.totalFiles;
        }
      });
    });
    console.log(arbol);

    /*
    nextTick(() => {
      if (autoFocusRef.value) {
        autoFocusRef.value.focus();
      }
    });
    */
  }).catch((error) => {
    paths.value = [];
    totalPages.value = 0;
    totalResults.value = 0;
    $q.notify({
      type: "negative",
      message: t("API Error: error loading artists"),
      caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
    });
    loading.value = false;
    lastChangesTimestamp.value = Date.now();
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
  if (filter.value && !isExpanded.value) {
    onExpandAll();
  }
}

//getTree();

onMounted(() => {
  browse();
});

</script>