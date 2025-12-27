<template>
  <q-page>
    <q-card class="q-pa-none">
      <q-card-section>
        <BreadCrumb icon="queue_music" label="Current playlist" />
      </q-card-section>
      <q-separator />

      <q-card class="q-pa-sm">
        <q-btn-group spread class="q-mb-md">
          <q-btn size="md" no-caps outline color="dark" :label="showTopButtonBarLabels ? 'New' : undefined" title="New"
            icon="add" @click="onNew" />
          <q-btn size="md" no-caps outline color="dark" :label="showTopButtonBarLabels ? 'Clear' : undefined"
            title="Clear" icon="clear" :disable="!currentPlayListsStore.hasPlayLists" @click="onEmpty" />
          <q-btn size=" md" no-caps outline color="dark" :label="showTopButtonBarLabels ? 'Discover' : undefined"
            title="Discover" icon="bolt" :disable="!currentPlayListsStore.hasPlayLists" @click="onDiscover" />
          <q-btn size="md" no-caps outline color="dark" :label="showTopButtonBarLabels ? 'Randomize' : undefined"
            title="Randomize" icon="shuffle" :disable="true" @click="onRandomize" />
          <q-btn size="md" no-caps outline color="dark" :label="showTopButtonBarLabels ? 'Previous' : undefined"
            title="Previous" icon="skip_previous"
            :disable="!currentPlayListsStore.allowSkipPreviousItemOnActivePlayList" @click="onSkipPrevious" />
          <q-btn size="md" no-caps outline color="dark" :label="showTopButtonBarLabels ? 'Play' : undefined"
            title="Play" icon="play_arrow" :disable="currentPlayListsStore.playerIsPlaying" @click="onPlay" />
          <q-btn size="md" no-caps outline color="dark" :label="showTopButtonBarLabels ? 'Pause' : undefined"
            title="Pause" icon="pause" :disable="currentPlayListsStore.playerIsPaused" @click="onPause" />
          <q-btn size="md" no-caps outline color="dark" :label="showTopButtonBarLabels ? 'Stop' : undefined"
            title="Stop" icon="stop" :disable="currentPlayListsStore.playerIsStopped" @click="onStop" />
          <q-btn size="md" no-caps outline color="dark" :label="showTopButtonBarLabels ? 'Next' : undefined"
            title="Next" icon="skip_next" :disable="!currentPlayListsStore.allowSkipNextItemOnActivePlayList"
            @click="onSkipNext" />
          <PlayListColumnSettingsButton :label="showTopButtonBarLabels ? 'Columns' : null" title="Column settings" />
        </q-btn-group>

        <div v-if="currentPlayListsStore.hasPlayLists">
          <q-tabs dense align="left" v-model="tab" indicator-color="pink">
            <q-tab no-caps v-for="playList, playListIndex in currentPlayListsStore.playLists" :key="playList.id"
              :name="playList.id"
              :class="{ 'bg-grey-3': playListIndex === currentPlayListsStore.selectedPlayListIndex }">
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
          <q-tab-panels v-model="tab" class="q-mt-xs">
            <q-tab-panel :name="playList.id" v-for="playList, playListIndex in currentPlayListsStore.playLists"
              :key="playList.id" class="q-pa-none">
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

    </q-card>
  </q-page>
</template>

<script setup lang="ts">
  import { computed } from "vue";
  import { useQuasar } from "quasar";
  //import { useI18n } from "vue-i18n";
  import { default as BreadCrumb } from "src/components/BreadCrumb.vue";
  import { useCurrentPlayListsStore } from "src/stores/currentPlayLists";
  import { uid } from "quasar";
  import { default as PlayListColumnSettingsButton } from "src/components/Buttons/PlayListColumnSettingsButton.vue";
  import { default as PlayListTable } from "src/components/PlayListTable.vue";
  //const { t } = useI18n();

  const currentPlayListsStore = useCurrentPlayListsStore();

  const { screen } = useQuasar();

  const showTopButtonBarLabels = computed(() => screen.gt.lg);
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

  const onEmpty = async (): Promise<void> => {
    console.log("onEmpty");
    if (tab.value) {
      try {
        await currentPlayListsStore.empty(tab.value);
      } catch (e) {
        console.error(e);
      }
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

  const onSkipPrevious = async () => {
    if (!currentPlayListsStore.playerHasPreviousUserInteractions) {
      currentPlayListsStore.playerInteract();
    }
    try {
      await currentPlayListsStore.skipPreviousItemOnActivePlayList();
    } catch (e) {
      console.error(e);
    }
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

  const onSkipNext = async () => {
    if (!currentPlayListsStore.playerHasPreviousUserInteractions) {
      currentPlayListsStore.playerInteract();
    }
    try {
      await currentPlayListsStore.skipNextItemOnActivePlayList();
    } catch (e) {
      console.error(e);
    }
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