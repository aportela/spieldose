<template>
  <!--
  vinyl svg credits:
  https://commons.wikimedia.org/wiki/File:Vinyl_record.svg
  -->
  <div @click="onClick" class="vinyl-container cursor-pointer overflow-hidden relative-position full-width"
    style="background: url(vectors/Vinyl_record.svg) no-repeat; background-size: cover;"
    :class="{ 'vinyl-rotation': animated }">
    <q-img v-if="currentImage" :src="currentImage" @error="currentImage = null" :ratio="1"
      img-class="vinyl_mini_album_cover" spinner-color="pink" />
  </div>
</template>

<script setup lang="ts">

import { ref, watch, nextTick } from "vue";

interface VinylProps {
  animated?: boolean;
  image?: string | null;
};

const props = withDefaults(defineProps<VinylProps>(), {
  animated: false,
  image: null,
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
  emit('onClick');
};


</script>

<style lang="css">
div.vinyl-container {
  aspect-ratio: 1;
}

img.vinyl_mini_album_cover {
  width: 27%;
  height: 27%;
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translateX(-50%) translateY(-50%);
  border: 0px solid #454545;
  border-radius: 100%;
}

div.vinyl-rotation {
  animation: rotation 8s linear infinite;
  z-index: 1;
}

@keyframes rotation {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(359deg);
  }
}

@-webkit-keyframes rotate {
  from {
    -webkit-transform: rotate(0deg);
  }

  to {
    -webkit-transform: rotate(359deg);
  }
}
</style>