<template>
  <!--
  cassete vector credits:
  Patrick Schwarz (nablagrange) at https://pixabay.com/vectors/cassette-music-magnetic-tape-7576061/
  -->
  <div class="cassette-container cursor-pointer" @click="onClick">
    <div class="cassette-wheel" :class="{ 'cassette-wheel-animated': animated }"></div>
    <div class="cassette-wheel" :class="{ 'cassette-wheel-animated': animated }"></div>
  </div>
  <div class="label gochi-hand-regular" :style="{ color: currentFontColor }" v-if="hasLabels">
    <span class="artist">{{ topLabel }}</span>
    <span class="album">{{ bottomLabel }}</span>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from "vue";

interface CasseteTapeProps {
  animated?: boolean;
  image?: string | null;
  id: string;
  topLabel?: string | null;
  bottomLabel?: string | null;
};

const props = withDefaults(defineProps<CasseteTapeProps>(), {
  animated: false,
  image: null,
  topLabel: null,
  bottomLabel: null,
});

const emit = defineEmits(['click']);

const hasLabels = computed(() => props.topLabel && props.bottomLabel);

const currentFontColor = ref<string>("#000");

const getRandomColor = (): string => {
  const allowed = "ABCDEF0123456789";
  let S = "#";
  while (S.length < 7) {
    S += allowed.charAt(Math.floor(Math.random() * 16));
  }
  return S;
};

watch(() => props.id, () => {
  currentFontColor.value = getRandomColor();
});

const onClick = () => {
  emit("click");
};

// TODO: resize font on long labels
// TODO: more random "dark" colors
// TODO: default label "Spieldose awesome mix vol.1" if no track/stream data available
</script>

<style lang="css">
@import url('https://fonts.googleapis.com/css2?family=Gochi+Hand&display=swap');

.label {
  position: relative;
  top: -220px;
  left: 64px;
  z-index: 3;
}

.gochi-hand-regular {
  font-family: "Gochi Hand", cursive;
  font-weight: 400;
  font-style: normal;
  font-size: 22px;
  color: #04008f;
}

.artist {
  position: absolute;
  top: -16px;
  transform-origin: center;
  transform: rotate(-3deg);
  display: inline-block;
  width: 236px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  text-align: center;
}

.album {
  position: absolute;
  top: 3px;
  left: 16px;
  transform-origin: center;
  transform: rotate(-2deg);
  display: inline-block;
  width: 296px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  text-align: center;
}

.cassette-container {
  width: 410px;
  height: 250px;
  background-image: url('vectors/cassette.svg');
  background-position: center;
  background-size: cover;
  position: relative;
  overflow: hidden;
  z-index: 1;
}

.cassette-wheel {
  position: absolute;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background-color: transparent;
  background-image: url('vectors/cassette.svg');
  background-size: 410px 250px;
  background-repeat: no-repeat;

  z-index: 2;
}

.cassette-wheel-animated {
  animation: spin 3s infinite linear;
}

.cassette-wheel:first-child {
  _left: 97px;
  left: 23.6%;
  _top: 102px;
  top: 41%;
  transform: translateY(-50%) rotate(0deg);
  background-position: -104px -79px;
}

.cassette-wheel:last-child {
  _right: 97px;
  right: 23.6%;
  _top: 103px;
  top: 41%;
  transform: translateY(-50%) rotate(0deg);
  background-position: -256px -79px;
}

@keyframes spin {
  0% {
    transform: translateY(-50%) rotate(0deg);
  }

  100% {
    transform: translateY(-50%) rotate(360deg);
  }
}
</style>