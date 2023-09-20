<template>
  <div id="spieldose-sidebar-vinyl-container" class="cursor-pointer"
    :class="{ 'spieldose-sidebar-animation-rotation-infinite': animated }" :style="style" v-if="animation"
    @click="toggleMode" :title="t('Toggle art animation')">
    <q-img v-if="images.small" :src="images.small" @error="images.small = null" img-class="vinyl_mini_cover"
      no-spinner></q-img>
  </div>
  <div id="spieldose-sidebar-fullcover-container" class="cursor-pointer" v-else @click="toggleMode"
    :title="t('Toggle art animation')">
    <q-img v-if="images.normal" :src="images.normal" @error="images.normal = null" alt="Album cover" width="400px"
      height="400px" spinner-color="pink" />
    <q-img v-else src="images/vinyl.png" alt="Vinyl" width="400px" height="400px" spinner-color="pink" />
  </div>
</template>

<style>
div#spieldose-sidebar-vinyl-container {
  width: 400px;
  height: 400px;
  overflow: hidden;
}

div#spieldose-sidebar-vinyl-container img {
  width: 130px;
  height: 130px;
  top: 136px;
  position: relative;
  margin-left: 137px;
  border-radius: 100%;
}

div#spieldose-sidebar-fullcover-container {
  overflow: hidden;
}

div.spieldose-sidebar-animation-rotation-infinite {
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
import { useI18n } from "vue-i18n";


const props = defineProps({
  animation: Boolean,
  animated: Boolean,
  normalImage: String,
  smallImage: String
});

const emit = defineEmits(['change']);

const { t } = useI18n();

// custom style for avoiding "images/vinyl.png" asset loading error if we put this on the <style> block
const style = "background: url(images/vinyl.png) no-repeat; background-size: cover;";
const images = ref({
  normal: props.normalImage,
  small: props.smallImage
});
const normalImage = computed(() => props.normalImage || null);
const smallImage = computed(() => props.smallImage || null);

watch(smallImage, (newValue) => {
  images.value.small = newValue;
});

watch(normalImage, (newValue) => {
  images.value.normal = newValue;
});

function toggleMode() {
  emit('change');
}
</script>
