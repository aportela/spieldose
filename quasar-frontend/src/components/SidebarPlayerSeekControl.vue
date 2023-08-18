<template>
  <q-list>
    <q-item>
      <q-item-section side>
        00:00
      </q-item-section>
      <q-item-section>
        <q-slider v-model="elapsedSeconds" :min="0" :max="10" label />
      </q-item-section>
      <q-item-section side>
        00:00
      </q-item-section>
    </q-item>
  </q-list>
</template>


<script setup>
import { ref } from "vue";
const elapsedSeconds = ref(0);
</script>
