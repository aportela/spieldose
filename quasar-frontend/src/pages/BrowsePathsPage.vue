<template>
  <q-page>
    <q-card class="q-pa-lg">
      <q-breadcrumbs class="q-mb-lg">
        <q-breadcrumbs-el icon="home" label="Spieldose" />
        <q-breadcrumbs-el icon="person" label="Browse paths" />
      </q-breadcrumbs>
      <q-card-section v-if="directories && directories.length > 0">
        <div>
          <q-tree :nodes="directories" v-model:selected="selected" node-key="hash" label-key="name"
            children-key="children" no-transition @update:selected="onTreeNodeSelected">
            <template v-slot:default-header="prop">
              <div v-if="prop.node.totalFiles > 0">
                <q-icon name="playlist_play" /> <q-icon name="playlist_add" /> {{ prop.node.name }} ({{
                  prop.node.totalFiles }} total tracks)
              </div>
              <span v-else>{{ prop.node.name }}</span>
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
import { useQuasar } from "quasar";

import { useCurrentPlaylistStore } from 'stores/currentPlaylist'

const $q = useQuasar();

const currentPlaylist = useCurrentPlaylistStore();

const noPathsFound = ref(false);
const loading = ref(false);
const directories = ref([]);

const selected = ref(null);

function getTree() {
  noPathsFound.value = false;
  loading.value = true;
  api.path.getTree().then((success) => {
    directories.value = success.data.items;
    loading.value = false;
  }).catch((error) => {
    $q.notify({
      type: "negative",
      message: "API Error: error loading paths",
      caption: "API Error: fatal error details: HTTP {" + error.response.status + "} ({" + error.response.statusText + "})"
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
  if (node && node.id) {
    loading.value = true;
    api.track.search({ path: node.id }, 1, 0, false, 'title', 'ASC').then((success) => {
      currentPlaylist.saveElements(success.data.tracks);
      loading.value = false;
    }).catch((error) => {
      loading.value = false;
    });
  }
  return (true);
}

getTree();

</script>
