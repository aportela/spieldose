<template>
  <div id="analyzer-container" style="height: 50px;" class="cursor-pointer"
    @click="toggleMode"></div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import AudioMotionAnalyzer from 'audiomotion-analyzer';
import { usePlayer } from 'stores/player';

const player = usePlayer();
const audioElement = ref(player.getElement);
const analyzer = ref(null);
const mode = ref(4);

function createAnalyzer() {
  if (!analyzer.value) {
    analyzer.value = new AudioMotionAnalyzer(
      document.getElementById('analyzer-container'),
      {
        source: audioElement.value,
        mode: mode.value,
        height: 40,
        ledBars: true,
        trueLeds : true,
        peakLine: true,
        showScaleX: false,
        showScaleY: false,
        stereo: false,
        splitGradient: false,
        start: false,
        bgAlpha: 1,
        overlay: true,
        showBgColor: true,
        maxFPS: 30,
      }
    );
    const options = {
      bgColor: '#fff',  // background color (optional) - defaults to '#111'
      dir: 'v',         // add this property to create a horizontal gradient (optional)
      colorStops: [     // list your gradient colors in this array (at least 2 entries are required)
        '#d30320',      // colors may be defined in any valid CSS format
        '#e399a3'       // colors may be defined in any valid CSS format
      ]
    }
    analyzer.value.registerGradient('my-grad', options);
    analyzer.value.gradient = 'my-grad';
  }
  if (!analyzer.value.isOn) {
    analyzer.value.toggleAnalyzer();
  }
}

function toggleMode() {
  mode.value++;
  if (mode.value > 8) {
    mode.value = 0;
  }
  analyzer.value.setOptions({ mode: mode.value });
}
onMounted(() => {
  createAnalyzer();
});
</script>
