<template>
  <q-card class="q-pa-lg">
    <q-breadcrumbs class="q-mb-lg">
      <q-breadcrumbs-el icon="home" label="Spieldose" />
      <q-breadcrumbs-el icon="person" :label="t('Browse paths')" />
    </q-breadcrumbs>
    <q-card-section>

      <q-splitter v-model="splitterModel" style="height: 100%">

        <template v-slot:before>
          <div class="row q-pa-sm q-col-gutter-lg">
            <div class="col-6">
              <CustomInputSearch :disable="loading" hint="Search paths with specified condition"
                placeholder="Text condition" v-model="filter" @update:modelValue="onFilterChanged"></CustomInputSearch>
            </div>
            <div class="col-6">
              <q-btn-group spread>
                <q-btn size="md" outline label="Expand all" @click="onExpandAll"></q-btn>
                <q-btn size="md" outline label="Collapse all" @click="onCollapseAll"></q-btn>
              </q-btn-group>
            </div>
          </div>
          <div class="q-pa-md">
            <div class="q-pa-md q-gutter-sm">
              <q-tree ref="treeRef" :nodes="pathTree" node-key="id" label-key="label2" accordion no-transition
                :filter="filter" />
            </div>
          </div>
        </template>
        <template v-slot:after>
          <q-tab-panels v-model="selected" animated transition-prev="jump-up" transition-next="jump-up">
            <q-tab-panel name="Relax Hotel">
              <div class="text-h4 q-mb-md">Welcome</div>
              <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quis praesentium cumque magnam odio iure
                quidem, quod illum numquam possimus obcaecati commodi minima assumenda consectetur culpa fuga nulla
                ullam. In, libero.</p>
              <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quis praesentium cumque magnam odio iure
                quidem, quod illum numquam possimus obcaecati commodi minima assumenda consectetur culpa fuga nulla
                ullam. In, libero.</p>
            </q-tab-panel>

            <q-tab-panel name="Food">
              <div class="text-h4 q-mb-md">Food</div>
              <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quis praesentium cumque magnam odio iure
                quidem, quod illum numquam possimus obcaecati commodi minima assumenda consectetur culpa fuga nulla
                ullam. In, libero.</p>
              <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quis praesentium cumque magnam odio iure
                quidem, quod illum numquam possimus obcaecati commodi minima assumenda consectetur culpa fuga nulla
                ullam. In, libero.</p>
            </q-tab-panel>

            <q-tab-panel name="Room service">
              <div class="text-h4 q-mb-md">Room service</div>
              <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quis praesentium cumque magnam odio iure
                quidem, quod illum numquam possimus obcaecati commodi minima assumenda consectetur culpa fuga nulla
                ullam. In, libero.</p>
              <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quis praesentium cumque magnam odio iure
                quidem, quod illum numquam possimus obcaecati commodi minima assumenda consectetur culpa fuga nulla
                ullam. In, libero.</p>
              <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quis praesentium cumque magnam odio iure
                quidem, quod illum numquam possimus obcaecati commodi minima assumenda consectetur culpa fuga nulla
                ullam. In, libero.</p>
            </q-tab-panel>

            <q-tab-panel name="Room view">
              <div class="text-h4 q-mb-md">Room view</div>
              <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quis praesentium cumque magnam odio iure
                quidem, quod illum numquam possimus obcaecati commodi minima assumenda consectetur culpa fuga nulla
                ullam. In, libero.</p>
              <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quis praesentium cumque magnam odio iure
                quidem, quod illum numquam possimus obcaecati commodi minima assumenda consectetur culpa fuga nulla
                ullam. In, libero.</p>
            </q-tab-panel>
          </q-tab-panels>
        </template>
      </q-splitter>
    </q-card-section>
  </q-card>
</template>

<script setup>

import { ref, reactive, onMounted, computed, nextTick } from "vue";
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

const splitterModel = ref(50);


const pathTree = ref([]);


function browse() {
  warningNoItems.value = false;
  loading.value = true;
  api.browse.path({}, currentPageIndex.value, 32, sortField.value, sortOrder.value, skipCount.value).then((success) => {
    paths.value = success.data.data.items;
    if (success.data.data.pager) {
      totalPages.value = success.data.data.pager.totalPages;
      totalResults.value = success.data.data.pager.totalResults;
      warningNoItems.value = success.data.data.pager.totalResults < 1;
      skipCount.value = true;
    }
    loading.value = false;
    lastChangesTimestamp.value = Date.now();

    paths.value.forEach(item => {
      const subDirectories = item.name.split('\\').filter(Boolean);
      let currentTreeNode = pathTree.value;

      subDirectories.forEach((subDirectory, index) => {
        let foundNode = currentTreeNode.find(node => node.label === subDirectory);

        if (!foundNode) {
          foundNode = {
            _id: uid(),
            path: item.name,
            name: subDirectory,
            label: subDirectory,
            label2: subDirectory + " (" + item.totalFiles + " track/s)",
            id: item.id,
            totalFiles: 0,
            children: []
          };
          currentTreeNode.push(foundNode);
        }

        currentTreeNode = foundNode.children;

        if (index === subDirectories.length - 1) {
          /*
          foundNode.children.push({
            label: subDirectory,
            id: item.id,
            totalFiles: item.totalFiles,
            //children: []
          });
          */
        }
      });
    });
    nextTick(() => {
      treeRef.value?.expandAll();
    });

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
  treeRef.value?.expandAll();
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