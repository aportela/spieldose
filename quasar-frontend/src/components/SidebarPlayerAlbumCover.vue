<template>
  <div id="vinyl_container" :class="{ 'rotation_enabled': rotateVinyl }" :style="style" v-if="showVinyl"
    @click="toggleMode">
    <q-img v-if="coverURLSmall" :src="coverURLSmall" @error="coverURLSmall = null" img-class="vinyl_mini_cover"
      no-spinner></q-img>
  </div>
  <div id="cover_container" v-else @click="toggleMode">
    <q-img v-if="coverURL" :src="coverURL" @error="coverURL = null" alt="Album cover" width="400px" height="400px"
      spinner-color="pink" />
    <q-img v-else src="images/vinyl.png" alt="Vinyl" width="400px" height="400px" />
  </div>
</template>

<style>
div#vinyl_container {
  width: 400px;
  height: 400px;
  overflow: hidden;
}

div#vinyl_container img.vinyl_mini_cover {
  width: 130px;
  height: 130px;
  top: 136px;
  position: relative;
  margin-left: 137px;
  border-radius: 100%;
}

div#cover_container {
  overflow: hidden;
}

div.rotation_enabled {
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

<script setup>
import { ref, computed, watch } from "vue";

// custom style for avoiding "images/vinyl.png" asset loading error if we put this on the <style> block
const style = "background: url(images/vinyl.png) no-repeat; background-size: cover;";

const props = defineProps({
  rotateVinyl: Boolean,
  smallVinylImage: String,
  coverImage: String
});

const coverURLSmall = ref(null);
const coverURL = ref(null);

const smallVinylImage = computed(() => {
  return (props.smallVinylImage);
});

const coverImage = computed(() => {
  return (props.coverImage);
});

watch(smallVinylImage, (newValue) => {
  coverURLSmall.value = newValue;
});

watch(coverImage, (newValue) => {
  coverURL.value = newValue;
});


const showVinyl = ref(false);

function toggleMode() {
  showVinyl.value = !showVinyl.value;
}
</script>
