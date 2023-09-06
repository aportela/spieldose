<template>
  <tr v-if="element.track" class="non-selectable" :class="{ 'bg-pink text-white': selected }">
    <td class="text-right cursor-pointer" @click="setcurrentIndex(index)">
      <q-icon :name="icon" size="sm" class="q-mr-sm" v-if="selected"></q-icon>{{ index + 1 }}/32
    </td>
    <td class="text-left cursor-pointer" @click="setcurrentIndex(index)">{{ element.track.title }}</td>
    <td class="text-left"><router-link v-if="element.track.artist && element.track.artist.name"
        :class="{ 'text-white text-bold': selected }"
        :to="{ name: 'artist', params: { name: element.track.artist.name } }"><q-icon name="link"
          class="q-mr-sm"></q-icon>{{
            element.track.artist.name }}</router-link></td>
    <td class="text-left"><router-link v-if="element.track.album && element.track.album.artist.name"
        :class="{ 'text-white text-bold': selected }"
        :to="{ name: 'artist', params: { name: element.track.album.artist.name } }"><q-icon name="link"
          class="q-mr-sm"></q-icon>{{ element.track.album.artist.name }}</router-link></td>
    <td class="text-left">{{ element.track.album ? element.track.album.title : null }}<span class="is-clickable"><i
          class="fas fa-link ml-1"></i></span></td>
    <td class="text-right">{{ element.track.trackNumber }}</td>
    <td class="text-right">{{ element.track.album ? element.track.album.year : null }}</td>
    <td class="text-center">
      <q-btn-group outline>
        <q-btn size="sm" color="white" text-color="grey-5" icon="north" title="Up" disabled />
        <q-btn size="sm" color="white" text-color="grey-5" icon="south" title="Down" disabled />
        <q-btn size="sm" color="white" text-color="grey-5" icon="favorite" title="Toggle favorite" disabled />
        <q-btn size="sm" color="white" text-color="grey-5" icon="download" title="Download" :disable="disabled"
          :href="element.track.url" />
      </q-btn-group>
    </td>
  </tr>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  disabled: Boolean,
  index: Number,
  selected: Boolean,
  isPlaying: Boolean,
  isPaused: Boolean,
  isStopped: Boolean,
  element: Object
});

const icon = computed(() => {
  if (props.isPlaying) {
    return ('play_arrow');
  } else if (props.isPaused) {
    return ('pause');
  } else if (props.isStopped) {
    return ('stop');
  } else {
    return ('play_arrow');
  }
});

const emit = defineEmits(['changeIndex']);

function setcurrentIndex(index) {
  emit('changeIndex', index);
}
</script>
