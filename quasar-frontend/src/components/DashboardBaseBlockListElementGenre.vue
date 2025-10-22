<template>
  <li class="is-size-6-5 dashboard_list_item" v-if="genre">
    <slot name="prepend">
    </slot>
    <q-icon name="tag" size="sm" />
    <span>{{ genre.name }}</span>
    <slot name="append">
    </slot>
  </li>
</template>

<script setup>

const props = defineProps({
  genre: {
    type: Object
  }
})

</script>

<style lang="css">
li.dashboard_list_item {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>