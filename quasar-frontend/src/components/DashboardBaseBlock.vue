<template>
<q-card class="my-card">
  <q-card-section class="bg-grey-3 text-black">
    <div class="text-h6"><q-icon :name="icon" class="cursor-pointer q-mr-sm"></q-icon>{{ title }}<q-icon name="refresh" class="cursor-pointer"></q-icon></div>
  </q-card-section>

  <q-card-section class="bg-white text-black">
    <slot name="body">
      <p>There is not enough data to calculate the statistics</p>
    </slot>
  </q-card-section>
</q-card>
</template>

<script setup>

const props = defineProps({
  icon: {
    type: String,
    required: true
  },
  title: {
    type: String,
    required: true
  }
})

</script>
