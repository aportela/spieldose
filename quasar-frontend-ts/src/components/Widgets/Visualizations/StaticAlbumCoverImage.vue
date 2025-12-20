<template>
  <div :class="{ 'cursor-pointer': clickable }" @click="onClick">
    <q-img v-if="currentImage" :src="currentImage" @error="currentImage = failbackImage ?? null" :ratio="1" width="100%"
      spinner-color="pink" />
    <div v-else class="no-image-or-error row items-center justify-center">
      [ no image ]
    </div>
  </div>
</template>

<script setup lang="ts">
  import { nextTick, ref, watch } from "vue";

  interface StaticAlbumCoverImageProps {
    image?: string | null;
    failbackImage?: string | null;
    clickable?: boolean;
  };

  const props = withDefaults(defineProps<StaticAlbumCoverImageProps>(), {
    image: null,
    failbackImage: null,
    clickable: false,
  });

  const emit = defineEmits(['onClick']);

  const currentImage = ref<string | null>(props.image ?? props.failbackImage ?? null);

  watch(() => props.image, (newValue: string | null) => {
    currentImage.value = null;
    if (newValue || props.failbackImage) {
      nextTick()
        .then(() => {
          currentImage.value = newValue ?? props.failbackImage;
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

<style lang="css">
  @import url('https://fonts.googleapis.com/css2?family=Gochi+Hand&display=swap');

  .no-image-or-error {
    width: 98%;
    height: auto;
    aspect-ratio: 1;
    font-family: "Gochi Hand", cursive;
    font-size: 3em;
  }

</style>