<template>
  <q-btn-dropdown outline no-caps label="Columns" icon="settings">
    <q-list>
      <q-item dense v-for="column in availablePlayListTableColumns" :key="column.name" v-show="column.name !== 'index'"
        clickable @click="onToggleColumnVisibility(column)">
        <q-icon :name="column.visible ? 'visibility' : 'visibility_off'" size="xs" class="q-mr-sm"
          :class="{ 'text-pink': column.visible, 'text-grey-6': !column.visible }" />
        {{ column.label }}
      </q-item>
    </q-list>
  </q-btn-dropdown>
</template>

<script setup lang="ts">
  import { type PlayListTableColumn, availablePlayListTableColumns } from "src/types/common";
  import { usePlayListVisibleColumnsStore } from "src/stores/playListVisibleColumns";

  const playListVisibleColumnsStore = usePlayListVisibleColumnsStore();

  const onToggleColumnVisibility = (column: PlayListTableColumn): void => {
    column.visible = !column.visible;
  };

</script>