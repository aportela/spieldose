<template>
  <q-btn-dropdown outline no-caps label="Columns" icon="settings">
    <q-list>
      <q-item dense v-for="column in playListVisibleColumnsStore.availableColumnDefinitions" :key="column.name"
        v-show="column.name !== 'index'" clickable @click="onToggleColumnVisibility(column.name)">
        <q-icon
          :name="playListVisibleColumnsStore.visibleColumnNames.includes(column.name) ? 'visibility' : 'visibility_off'"
          size="xs" class="q-mr-sm"
          :class="{ 'text-pink': playListVisibleColumnsStore.visibleColumnNames.includes(column.name), 'text-grey-6': !playListVisibleColumnsStore.visibleColumnNames.includes(column.name) }" />
        {{ column.label }}
      </q-item>
    </q-list>
  </q-btn-dropdown>
</template>

<script setup lang="ts">
  import { type PlayListTableColumnName, usePlayListVisibleColumnsStore } from "src/stores/playListVisibleColumns";

  const playListVisibleColumnsStore = usePlayListVisibleColumnsStore();

  const onToggleColumnVisibility = (columnName: PlayListTableColumnName): void => {
    playListVisibleColumnsStore.toggleColumnVisibility(columnName);
  };

</script>