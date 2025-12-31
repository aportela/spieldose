<template>
  <q-list>
    <q-item>
      <q-item-section side>
        {{ audioCurrentTimeLabel }}
      </q-item-section>
      <q-item-section>
        <q-slider :disable="disabled" v-model="currentTime" :min="0" :max="currentPlayListsStore.audioDuration"
          :step="1" label :label-value="audioCurrentTimeLabel" />
      </q-item-section>
      <q-item-section side>{{ audioDurationLabel }}</q-item-section>
    </q-item>
  </q-list>
</template>

<script setup lang="ts">
  import { computed } from "vue";
  import { useCurrentPlayListsStore } from "src/stores/currentPlayLists";
  import { formatSecondsAsTime } from "src/composables/format";

  const currentPlayListsStore = useCurrentPlayListsStore();

  interface SeekControlProps {
    disabled?: boolean;
  }

  withDefaults(defineProps<SeekControlProps>(), {
    disabled: false,
  });

  const currentTime = computed({
    get() {
      return Math.floor(currentPlayListsStore.audioCurrentTime)
    },
    set(value: number | null) {
      if (!currentPlayListsStore.playerHasPreviousUserInteractions) {
        currentPlayListsStore.playerInteract();
      }
      currentPlayListsStore.setAudioCurrentTime(value);
    }
  });

  const audioCurrentTimeLabel = computed(() => formatSecondsAsTime(Math.floor(currentPlayListsStore.audioCurrentTime)));
  const audioDurationLabel = computed(() => formatSecondsAsTime(Math.floor(currentPlayListsStore.audioDuration)));

</script>