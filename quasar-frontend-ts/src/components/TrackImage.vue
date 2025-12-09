<template>
  <q-img :src="url" ratio="1" @error="error = true" placeholder-src="#"
    :class="{ 'round': round, 'rotate': round && rotate }" />
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

interface TrackImageProps {
  src: string | null;
  round?: boolean;
  rotate?: boolean;
};

const props = withDefaults(defineProps<TrackImageProps>(), {
  round: false,
  rotate: false,
});

const defaultImage = "vectors/Vinyl_record.svg";
const error = ref<boolean>(false);
const url = computed(() => props.src ? (!error.value ? props.src : defaultImage) : defaultImage);

</script>

<style lang="css">
.round {
  border: 1px solid #222;
  border-radius: 50%;

}

.rotate {
  animation: rotation 8s linear infinite;
}

@keyframes rotation {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(359deg);
  }
}
</style>