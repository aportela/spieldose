<template>
  <div :class="{ 'cursor-pointer': clickable }" @click="onClick">
    <q-img v-if="currentImage" :src="currentImage" @error="currentImage = failbackImage ?? null" :ratio="1" width="100%"
      spinner-color="pink" />
    <div v-else class="no-image overflow-hidden">
      <p class="q-mt-xl no-image-label no-image-top-label text-grey-7">Spieldose</p>
      <p class="q-mt-xl no-image-label no-image-bottom-label text-pink">Awesome Mix Vol. {{ currentDayOfYear }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { nextTick, ref, watch, computed } from "vue";
import { date } from "quasar";

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

const currentDayOfYear = computed(() => date.formatDate(new Date(), "DDD"));

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

.no-image {
  width: 98%;
  margin: 1% auto;
  height: auto;
  aspect-ratio: 1;
}

.no-image-label {
  font-family: "Gochi Hand", cursive;
  font-weight: 400;
  font-style: normal;
  width: 100%;
  white-space: nowrap;
  text-align: center;
  display: inline-block;
  transform-origin: center;
}

.no-image-top-label {
  color: hsl(46, 93%, 45%);
  font-size: 6em;
  transform: rotate(-5deg);
}


.no-image-bottom-label {
  font-size: 3em;
  transform: rotate(-3deg);
}
</style>