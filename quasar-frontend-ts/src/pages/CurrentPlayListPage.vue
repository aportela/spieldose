<template>
  <q-page>
    <BreadCrumb icon="queue_music" label="Current playlist" />
    <q-card class="q-pa-lg">
      <q-btn-group spread class="q-mb-md">
        <q-btn size="md" no-caps outline color="dark" label="New" icon="add" @click="onNew" />
        <q-btn size="md" no-caps outline color="dark" label="Clear" icon="clear"
          :disable="!currentPlayListsStore.hasPlayLists" @click="onEmpty" />
        <q-btn size=" md" no-caps outline color="dark" label="Discover" icon="bolt"
          :disable="!currentPlayListsStore.hasPlayLists" @click="onDiscover" />
        <q-btn size="md" no-caps outline color="dark" label="Randomize" icon="shuffle" :disable="true"
          @click="onRandomize" />
        <q-btn size="md" no-caps outline color="dark" label="Previous" icon="skip_previous"
          :disable="!currentPlayListsStore.allowSkipPreviousItemOnActivePlayList" @click="onSkipPrevious" />
        <q-btn size="md" no-caps outline color="dark" label="Play" icon="play_arrow"
          :disable="currentPlayListsStore.playerIsPlaying" @click="onPlay" />
        <q-btn size="md" no-caps outline color="dark" label="Pause" icon="pause"
          :disable="currentPlayListsStore.playerIsPaused" @click="onPause" />
        <q-btn size="md" no-caps outline color="dark" label="Stop" icon="stop"
          :disable="currentPlayListsStore.playerIsStopped" @click="onStop" />
        <q-btn size="md" no-caps outline color="dark" label="Next" icon="skip_next"
          :disable="!currentPlayListsStore.allowSkipNextItemOnActivePlayList" @click="onSkipNext" />
        <PlayListColumnSettingsButton />
      </q-btn-group>

      <div v-if="playListsFound">
        <q-tabs dense align="left" v-model="tab" indicator-color="pink">
          <q-tab no-caps v-for="playList, playListIndex in currentPlayListsStore.playLists" :key="playList.id"
            :name="playList.id">
            <q-badge :color="currentPlayListsStore.playLists[playListIndex]?.items.length ? 'grey-7' : 'red'"
              floating>{{
                currentPlayListsStore.playLists[playListIndex]?.items.length
              }}</q-badge>
            <div class="row q-pa-none" align="center">
              <div class="col">
                <div class="q-gutter-none">
                  <q-toolbar class="q-pa-none">
                    <q-icon name="speaker" color="dark" class="q-mr-sm" size="md"
                      :class="{ 'zoom-infinite': currentPlayListsStore.playerIsPlaying }"
                      v-if="currentPlayListsStore.activePlayListIndex == playListIndex" />
                    <span>
                      {{ playList.name }}
                    </span>
                    <q-space />
                    <q-btn size="sm" flat icon="save"
                      @click.stop="currentPlayListsStore.savePlayListAtIndex(playListIndex)" />
                    <q-btn size="sm" flat icon="delete"
                      @click.stop="currentPlayListsStore.removePlayListAtIndex(playListIndex)" />
                    <q-btn size="sm" flat icon="close"
                      @click.stop="currentPlayListsStore.closePlayListAtIndex(playListIndex)" />
                  </q-toolbar>
                </div>
              </div>
            </div>
          </q-tab>
        </q-tabs>
        <q-tab-panels v-model="tab">
          <q-tab-panel :name="playList.id" v-for="playList, playListIndex in currentPlayListsStore.playLists"
            :key="playList.id">
            <PlayListTable :playList="playList" :active="currentPlayListsStore.activePlayListIndex === playListIndex"
              :play-list-current-item-index="currentPlayListsStore.activePlayListItemIndex"
              @on-click-item-at-index="(index: number) => currentPlayListsStore.selectPlayListItem(playListIndex, index)"
              @on-action-move-up-item-at-index="(index: number) => currentPlayListsStore.moveUpPlayListItem(playListIndex, index)"
              @on-action-move-down-item-at-index="(index: number) => currentPlayListsStore.moveDownPlayListItem(playListIndex, index)"
              @on-action-remove-item-at-index="(index: number) => currentPlayListsStore.removePlayListItem(playListIndex, index)"
              @on-action-toggle-favorite-item-at-index="(index: number) => currentPlayListsStore.toggleFavoritePlayListItem(playListIndex, index)" />
          </q-tab-panel>
        </q-tab-panels>
      </div>
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
  import { computed } from "vue";
  //import { useI18n } from "vue-i18n";
  import { default as BreadCrumb } from "src/components/BreadCrumb.vue";
  import { useCurrentPlayListsStore } from "src/stores/currentPlayLists";
  import { uid } from "quasar";
  import { default as PlayListColumnSettingsButton } from "src/components/Buttons/PlayListColumnSettingsButton.vue";
  import { default as PlayListTable } from "src/components/PlayListTable.vue";
  //const { t } = useI18n();

  const currentPlayListsStore = useCurrentPlayListsStore();

  const playListsFound = computed(() => currentPlayListsStore.hasPlayLists);

  const tab = computed({
    get() {
      return currentPlayListsStore.hasPlayLists ? currentPlayListsStore.playLists[currentPlayListsStore.selectedPlayListIndex]?.id ?? null : null;
    },
    set(value: string) {
      currentPlayListsStore.setSelectedPlayListId(value);
    }
  });

  const onNew = async (): Promise<void> => {
    console.log("onNew");
    const PlayListId = uid();
    try {
      await currentPlayListsStore.add(PlayListId, `New playlist ${currentPlayListsStore.playLists.length + 1}`);
    } catch (e) {
      console.error(e);
    }
  };

  const onEmpty = (): void => {
    console.log("onEmpty");
    if (tab.value) {
      currentPlayListsStore.empty(tab.value);
    } else {
      console.error("Invalid tab", tab.value);
    }
  };

  const onDiscover = (): void => {
    console.log("onDiscover");
    if (tab.value) {
      currentPlayListsStore.randomFill(tab.value).then(() => { }).catch((error) => { console.error(error); }).finally(() => { });
    } else {
      console.error("Invalid tab", tab.value);
    }
  };

  const onRandomize = (): void => {
    if (!currentPlayListsStore.playerHasPreviousUserInteractions) {
      currentPlayListsStore.playerInteract();
    }
    console.log("onRandomize");
  };

  const onSkipPrevious = (): void => {
    if (!currentPlayListsStore.playerHasPreviousUserInteractions) {
      currentPlayListsStore.playerInteract();
    }
    currentPlayListsStore.skipPreviousItemOnActivePlayList();
  };

  const onPlay = (): void => {
    if (!currentPlayListsStore.playerHasPreviousUserInteractions) {
      currentPlayListsStore.playerInteract();
    }
    currentPlayListsStore.playerActionPlay(true);
  };

  const onPause = (): void => {
    if (!currentPlayListsStore.playerHasPreviousUserInteractions) {
      currentPlayListsStore.playerInteract();
    }
    currentPlayListsStore.playerActionPause();
  };

  const onStop = (): void => {
    if (!currentPlayListsStore.playerHasPreviousUserInteractions) {
      currentPlayListsStore.playerInteract();
    }
    currentPlayListsStore.playerActionStop();
  };

  const onSkipNext = (): void => {
    if (!currentPlayListsStore.playerHasPreviousUserInteractions) {
      currentPlayListsStore.playerInteract();
    }
    currentPlayListsStore.skipNextItemOnActivePlayList();
  };

</script>

<style lang="css">
  .zoom-infinite {
    animation: zoomEffect 2s ease-in-out infinite;
  }

  @keyframes zoomEffect {
    0% {
      transform: scale(1);
    }

    50% {
      transform: scale(0.8);
    }

    100% {
      transform: scale(1);
    }
  }
</style>
