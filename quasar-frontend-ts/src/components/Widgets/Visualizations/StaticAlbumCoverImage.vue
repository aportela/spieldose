<template>
  <div :class="{ 'cursor-pointer': clickable }" @click="onClick">
    <q-img v-if="currentImage" :src="currentImage" @error="currentImage = null" alt="Album cover" :ratio="1"
      width="100%" spinner-color="pink" />
    <q-img v-else src="vectors/Vinyl_record.svg" alt="Vinyl" :ratio="1" width="100%" spinner-color="pink" />
  </div>
</template>

<script setup lang="ts">
import { nextTick, ref, watch } from "vue";

interface StaticAlbumCoverImageProps {
  image?: string | null;
  clickable?: boolean;
};

const props = withDefaults(defineProps<StaticAlbumCoverImageProps>(), {
  image: null,
  clickable: false,
});

const emit = defineEmits(['onClick']);

const currentImage = ref<string | null>(props.image);

watch(() => props.image, (newValue: string | null) => {
  currentImage.value = null;
  if (newValue) {
    nextTick()
      .then(() => {
        currentImage.value = newValue;
      }).catch((e) => {
        console.error(e);
      });
  }
});

const onClick = () => {
  if (props.clickable) {
    emit('onClick');
  }
};

</script>

<style lang="css"></style>