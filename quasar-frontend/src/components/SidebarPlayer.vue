<template>
  <div>
    <div id="rotating_album_cover" :style="style" v-if="customVinyl" @click.prevent="customVinyl = !customVinyl">
      <img :src="coverURL">
    </div>
    <div id="album_cover" v-else @click.prevent="customVinyl = !customVinyl">
      <img v-if="coverURL" :src="coverURL" alt="Album cover" @error="coverURL = null" />
      <img v-else src="images/vinyl.png" alt="Vinyl" />
    </div>
    <q-list>
      <q-item>
        <q-item-section side>
          <q-icon :name="volumeIcon" />
        </q-item-section>
        <q-item-section>
          <q-slider v-model="volume" :min="0" :max="10" label />
        </q-item-section>
        <q-item-section side>
          {{ volume * 10 }}%
        </q-item-section>
      </q-item>
    </q-list>

    <div id="song_info">
      <p class="song_info_track text-center text-weight-bolder" style="color: #d30320;">Won’t Get Fooled Again</p>
      <p class="song_info_album text-center">Who’s Next</p>
      <p class="song_info_artist text-center"><a href="#/app/artist/The%20Who" style="text-decoration: none;">The Who</a>
      </p>
    </div>
  </div>
</template>

<style>
img {
  width: 400px;
  height: 400px;
}

div#rotating_album_cover {
  display: block;
  width: 400px;
  height: 400px;
  background-size: auto;
  background-size: cover;
}

div#rotating_album_cover img {
  width: 130px;
  height: 130px;
  top: 136px;
  position: relative;
  margin-left: 137px;
  border-radius: 100%;
}

div#album_cover img {
  width: 400px;
  height: 400px;
  display: block;
}
</style>

<script setup>
import { ref, computed } from "vue";

const volume = ref(8);
const style = "background: url(images/vinyl.png) no-repeat; background-size: auto; background-size: cover;";

const coverURL = "http://127.0.0.1:8081/api/2/cache/thumbnail/small/d7af7e7a3c07f69f56beaa92b3029d173e7c9d8e";
const customVinyl = ref(true);

const volumeIcon = computed(() => {
  if (volume.value == 0) {
    return ('volume_off');
  } else if (volume.value < 4) {
    return ('volume_mute');
  } else if (volume.value < 7) {
    return ('volume_down');
  } else {
    return ('volume_up');
  }
});

</script>
