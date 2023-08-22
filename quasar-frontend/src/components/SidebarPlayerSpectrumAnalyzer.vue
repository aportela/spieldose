<template>
  <div id="analyzer-container" style="height: 50px;" class="cursor-pointer" @click="onChangeaudioElementMotionAnalyzerMode"></div>
</template>

<script setup>
import { ref  } from "vue";
import AudioMotionAnalyzer from 'audiomotion-analyzer';
import { usePlayer } from 'stores/player';

const player = usePlayer();

const audioElement = ref(player.getElement);

const audioElementMotion = ref(null);

const audioElementMotionMode = ref(4);

function createAnalyzer() {
  if (!audioElementMotion.value) {
    audioElementMotion.value = new AudioMotionAnalyzer(
      document.getElementById('analyzer-container'),
      {
        source: audioElement.value,
        mode: audioElementMotionMode.value,
        height: 40,
        ledBars: false,
        showScaleX: false,
        showScaleY: false,
        stereo: false,
        splitGradient: false,
        start: false,
        bgAlpha: 1,
        overlay: true,
        showBgColor: true
      }
    );
    const options = {
      /*
      bgColor: '#011a35', // background color (optional) - defaults to '#111'
      dir: 'h',           // add this property to create a horizontal gradient (optional)
      colorStops: [       // list your gradient colors in this array (at least 2 entries are required)
          'red',                      // colors may be defined in any valid CSS format
          { pos: .6, color: '#ff0' }, // use an object to adjust the position (0 to 1) of a color
          'hsl( 120, 100%, 50% )'     // colors may be defined in any valid CSS format
      ]
      */
      bgColor: '#fff', // background color (optional) - defaults to '#111'
      dir: 'v',           // add this property to create a horizontal gradient (optional)
      colorStops: [       // list your gradient colors in this array (at least 2 entries are required)
        '#d30320', // colors may be defined in any valid CSS format
        //'#020024',
        '#e399a3'                      // colors may be defined in any valid CSS format

      ]
    }
    audioElementMotion.value.registerGradient('my-grad', options);
    audioElementMotion.value.gradient = 'my-grad';
  }
  if (!audioElementMotion.value.isOn) {
    console.log(1);
    audioElementMotion.value.toggleAnalyzer();
    console.log(2);
  }
}

function onChangeaudioElementMotionAnalyzerMode() {
  audioElementMotionMode.value++;
  if (audioElementMotionMode.value > 8) {
    audioElementMotionMode.value = 0;
  }
  audioElementMotion.value.setOptions({ mode: audioElementMotionMode.value });
  console.log("mode: " + audioElementMotionMode.value);
}
createAnalyzer();
</script>
