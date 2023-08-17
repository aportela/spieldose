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
    <div id="player_controls">
      <ul class="list list--buttons">
        <li><a href="#" class="list__link"><q-icon name="skip_previous"></q-icon></a></li>
        <li><a href="#" class="list__link" style=""><q-icon name="play_arrow"></q-icon></a></li>
        <li><a href="#" class="list__link"><q-icon name="skip_next"></q-icon></a></li>
      </ul>
    </div>
    <q-list>
      <q-item>
        <q-item-section side>
          00:00
        </q-item-section>
        <q-item-section>
          <q-slider v-model="elapsedSeconds" :min="0" :max="10" label />
        </q-item-section>
        <q-item-section side>
          00:00
        </q-item-section>
      </q-item>
    </q-list>
    <div id="current_track_actions">
      <ul class="list list--footer">
        <li class="text-h5"><q-icon name="reorder" title="Toggle navigation menu"></q-icon></li>
        <li class="text-h5"><q-icon name="bar_chart" title="Toggle analyzer"></q-icon></li>
        <li class="text-h5"><q-icon name="favorite" title="Love/unlove track"></q-icon></li>
        <li class="text-h5"><q-icon name="shuffle" title="Toggle random sort"></q-icon></li>
        <li class="text-h5"><q-icon name="replay" title="Toggle repeat mode"></q-icon></li>
        <li class="text-h5"><q-icon name="file_download" title="Download track"></q-icon></li>
        <li class="text-h5"><q-icon name="screenshot_monitor" title="Toggle section details"></q-icon></li>
      </ul>
    </div>
  </div>
</template>

<style>
img {
  width: 400px;
  height: 400px;
}

div#player_controls {
  padding-bottom: 2rem;
}

.list--buttons {
  align-items: center;
  justify-content: center;
}

.list {
  display: flex;
  margin: 0;
  padding: 0;
  list-style-type: none;
}

ul {
  list-style: none;
  list-style-type: none;
}

.list--buttons li:first-of-type a,
.list--buttons li:last-of-type a {
  font-size: 0.95rem;
  color: #212121;
  opacity: 0.5;
}

.list--buttons li:nth-of-type(n+2) {
  margin-left: 1.25rem;
}

.list--buttons a {
  padding-top: 0.45rem;
  padding-right: 0.75rem;
  padding-bottom: 0.45rem;
  padding-left: 0.75rem;
  font-size: 1rem;
  border-radius: 50%;
  box-shadow: 0 3px 6px rgba(33, 33, 33, 0.1), 0 3px 12px rgba(33, 33, 33, 0.15);
}

.list__link {
  transition: all 0.25s cubic-bezier(0.4, 0, 1, 1);
}

a {
  color: #485fc7;
  cursor: pointer;
  text-decoration: none;
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

div#current_track_actions {
  padding: 1rem 4rem;
}
div#current_track_actions ul {
  display: flex;
  margin: 0;
  padding: 0;
  list-style-type: none;
  justify-content: space-between;
}
</style>

<script setup>
import { ref, computed } from "vue";

const volume = ref(8);
const elapsedSeconds = ref(0);
const style = "background: url(images/vinyl.png) no-repeat; background-size: auto; background-size: cover;";

const coverURL = "http://127.0.0.1:8081/api/2/cache/thumbnail/small/d7af7e7a3c07f69f56beaa92b3029d173e7c9d8e";
const customVinyl = ref(false);

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
