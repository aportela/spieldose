<template>
  <div id="rotating_album_cover" :class="{ 'is_rotating_album_cover': rotate }" :style="style" v-if="customVinyl"
    @click.prevent="customVinyl = !customVinyl">
    <img :src="coverURLSmall" v-if="coverURLSmall" @error="coverURLSmall = null">
  </div>
  <div id="album_cover" v-else @click.prevent="customVinyl = !customVinyl">
    <q-img
        v-if="coverURL"
        :src="coverURL"
        spinner-color="pink"
        width="400px"
        height="400px"
        @error="coverURL = null"
      />
    <q-img v-else src="images/vinyl.png" alt="Vinyl" spinner-color="pink" width="400px"
        height="400px" />
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

div.is_rotating_album_cover {
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

div#analyzer-container {
  z-index: 2;
  margin-top: 4px;
  position: relative;
}
</style>

<script setup>
import { ref, computed, watch } from "vue";

const props = defineProps({
  trackId: String,
  rotate: Boolean
});

const coverURLSmall = ref(null);
const coverURL = ref(null);

const trackId = computed(() => {
  return (props.trackId);
});

watch(trackId, (newValue) => {
  coverURLSmall.value = "/api/2/track/thumbnail/small/" + newValue;
  coverURL.value = "/api/2/track/thumbnail/normal/" + newValue;
});

const style = "background: url(images/vinyl.png) no-repeat; background-size: auto; background-size: cover;";

const customVinyl = ref(false);

</script>
