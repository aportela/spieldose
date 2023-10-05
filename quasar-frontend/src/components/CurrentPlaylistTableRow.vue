<template>
  <tr v-if="element.track" class="non-selectable" :class="{ 'bg-pink text-white': selected }">
    <td class="text-right cursor-pointer" @click="setcurrentIndex">
      <q-icon :name="icon" size="sm" class="q-mr-sm" v-if="selected"></q-icon>{{ index + 1 }}/32
    </td>
    <td class="text-left cursor-pointer" @click="setcurrentIndex">{{ element.track.title }}</td>
    <td class="text-left"><router-link v-if="element.track.artist && element.track.artist.name"
        :class="{ 'text-white text-bold': selected }"
        :to="{ name: 'artist', params: { name: element.track.artist.name }, query: { mbid: element.track.artist.mbId, tab: 'overview' } }"><q-icon
          name="link" class="q-mr-sm"></q-icon>{{
            element.track.artist.name }}</router-link></td>
    <td class="text-left gt-lg"><router-link
        v-if="element.track.album && element.track.album.artist && element.track.album.artist.name"
        :class="{ 'text-white text-bold': selected }"
        :to="{ name: 'artist', params: { name: element.track.album.artist.name }, query: { mbid: element.track.album.artist.mbId, tab: 'overview' } }"><q-icon
          name="link" class="q-mr-sm"></q-icon>{{ element.track.album.artist.name }}</router-link></td>
    <td class="text-left">{{ element.track.album ? element.track.album.title : null }}<span class="is-clickable"><i
          class="fas fa-link ml-1"></i></span></td>
    <td class="text-right gt-lg">{{ element.track.trackNumber }}</td>
    <td class="text-right">{{ element.track.album ? element.track.album.year : null }}</td>
    <td class="text-center">
      <q-btn-group outline>
        <q-btn size="sm" color="white" text-color="grey-5" icon="north" :title="t('Up')" :disable="disabled || index == 0"
          @click="onUp" />
        <q-btn size="sm" color="white" text-color="grey-5" icon="south" :title="t('Down')"
          :disable="disabled || index == lastIndex - 1" @click="onDown" />
        <q-btn size="sm" color="white" :text-color="element.favorited ? 'pink' : 'grey-5'" icon="favorite"
          :title="t('Toggle favorite')" :disable="disabled" @click="onToggleFavorite" />
        <q-btn size="sm" color="white" text-color="grey-5" icon="download" :title="t('Download')" :disable="disabled"
          :href="element.track.url" />
        <q-btn size="sm" color="white" text-color="grey-5" icon="delete" :title="t('Remove')"
          :disable="disabled || (isPlaying && selected)" @click="onRemove" />
      </q-btn-group>
    </td>
  </tr>
  <tr v-else-if="element.radioStation" class="non-selectable" :class="{ 'bg-pink text-white': selected }">
    <td class="text-right cursor-pointer" @click="setcurrentIndex">
      <q-icon :name="icon" size="sm" class="q-mr-sm" v-if="selected"></q-icon>{{ index + 1 }}/32
    </td>
    <td colspan="2" class="text-left cursor-pointer" @click="setcurrentIndex">{{ element.radioStation.name }}</td>
    <td class="gt-lg"></td>
    <td></td>
    <td class="gt-lg"></td>
    <td></td>
    <td class="text-center">
      <q-btn-group outline>
        <q-btn size="sm" color="white" text-color="grey-5" icon="north" :title="t('Up')" :disable="disabled || index == 0"
          @click="onUp" />
        <q-btn size="sm" color="white" text-color="grey-5" icon="south" :title="t('Down')"
          :disable="disabled || index == lastIndex - 1" @click="onDown" />
        <q-btn size="sm" color="white" text-color="grey-5" icon="favorite" :title="t('Toggle favorite')"
          :disable="true" />
        <q-btn size="sm" color="white" text-color="grey-5" icon="link" :title="t('View url')" :disable="disabled"
          :href="element.radioStation.url" target="_blank" />
        <q-btn size="sm" color="white" text-color="grey-5" icon="delete" :title="t('Remove')"
          :disable="disabled || (isPlaying && selected)" @click="onRemove" />
      </q-btn-group>
    </td>
  </tr>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  disabled: Boolean,
  index: Number,
  lastIndex: Number,
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

const emit = defineEmits(['setcurrentIndex', 'up', 'down', 'toggleFavorite', 'remove']);

function setcurrentIndex() {
  emit('setcurrentIndex', props.index);
}

function onUp() {
  emit('up', props.index);
}

function onDown() {
  emit('down', props.index);
}

function onToggleFavorite() {
  emit('toggleFavorite', props.index);
}

function onRemove() {
  emit('remove', props.index);
}

</script>
