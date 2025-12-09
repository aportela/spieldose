<template>
  <q-img :src="url" ratio="1" @error="error = true" placeholder-src="#" />
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

interface TrackImageProps {
  src: string | null;
};

const props = defineProps<TrackImageProps>();
const defaultImage = "vectors/Vinyl_record.svg";
const error = ref<boolean>(false);
const url = computed(() => props.src ? (!error.value ? props.src : defaultImage) : defaultImage);

</script>