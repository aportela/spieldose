<template>
  <div id="rotating_album_cover" :style="style" v-if="customVinyl" @click.prevent="customVinyl = !customVinyl">
    <img :src="coverURL">
  </div>
  <div id="album_cover" v-else @click.prevent="customVinyl = !customVinyl">
    <img v-if="coverURL" :src="coverURL" alt="Album cover" @error="coverURL = null" />
    <img v-else src="images/vinyl.png" alt="Vinyl" />
  </div>
</template>

<style>
div#rotating_album_cover {
  display: block;
  width: 400px;
  height: 400px;
  background-size: auto;
  background-size: cover;
  overflow: hidden;
}

div#rotating_album_cover img {
  width: 130px;
  height: 130px;
  top: 136px;
  position: relative;
  margin-left: 137px;
  border-radius: 100%;
}

div#album_cover {
  overflow: hidden;
}

div#album_cover img {
  width: 400px;
  height: 400px;
  display: block;
}
</style>

<script setup>
import { ref, computed } from "vue";

const style = "background: url(images/vinyl.png) no-repeat; background-size: auto; background-size: cover;";

const coverURL = "http://127.0.0.1:8081/api/2/cache/thumbnail/small/d7af7e7a3c07f69f56beaa92b3029d173e7c9d8e";
const customVinyl = ref(false);

</script>
