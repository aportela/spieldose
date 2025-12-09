<template>
  <q-page>
    <BreadCrumb icon="queue_music" label="Current playlist" />
    <q-card class="q-pa-lg">
      <q-btn-group spread class="q-mb-md">
        <q-btn size="md" no-caps outline color="dark" label="New" icon="add" @click="onNew" />
        <q-btn size="md" no-caps outline color="dark" label="Clear" icon="clear"
          :disable="!currentPlayListStore.hasItems" @click="onEmpty" />
        <q-btn size=" md" no-caps outline color="dark" label="Discover" icon="bolt" @click="onDiscover" />
        <q-btn size="md" no-caps outline color="dark" label="Randomize" icon="shuffle"
          :disable="!currentPlayListStore.hasItems" @click="onRandomize" />
        <q-btn size="md" no-caps outline color="dark" label="Previous" icon="skip_previous"
          :disable="!currentPlayListStore.hasItems" @click="onSkipPrevious" />
        <q-btn size="md" no-caps outline color="dark" label="Play" icon="play_arrow"
          :disable="!currentPlayListStore.hasItems" @click="onPlay" />
        <q-btn size="md" no-caps outline color="dark" label="Pause" icon="pause"
          :disable="!currentPlayListStore.hasItems" @click="onPause" />
        <q-btn size="md" no-caps outline color="dark" label="Stop" icon="stop" :disable="!currentPlayListStore.hasItems"
          @click="onStop" />
        <q-btn size="md" no-caps outline color="dark" label="Next" icon="skip_next"
          :disable="!currentPlayListStore.hasItems" @click="onSkipNext" />
        <q-btn-dropdown outline no-caps label="Columns" icon="settings">
          <q-list>
            <q-item dense v-for="column in availableColumns" :key="column.name" v-show="column.name !== 'index'"
              clickable @click="onToggleColumnVisibility(column)">
              <q-icon :name="column.visible ? 'visibility' : 'visibility_off'" size="xs" class="q-mr-sm"
                :class="{ 'text-pink': column.visible, 'text-grey-6': !column.visible }" />
              <!--
              <q-icon name="arrow_drop_up" size="xs" class="cursor-pointer" />
              <q-icon name="arrow_drop_down" size="xs" class="cursor-pointer" />
              -->
              {{ column.label }}
            </q-item>
          </q-list>
        </q-btn-dropdown>
      </q-btn-group>

      <q-tabs dense align="left" v-model="currentPlayListTab" v-if="currentPlayListsStore.hasPlayLists">
        <q-tab no-caps v-for="playList, index in currentPlayListsStore.playLists" :key="playList.id"
          :name="playList.id">
          <div class="row q-pa-none" align="center">
            <div class="col">
              <div class="q-gutter-none">
                <q-toolbar class="q-pa-none">
                  <span>
                    {{ playList.name }}
                  </span>
                  <q-space />
                  <q-btn size="sm" flat icon="save" @click="currentPlayListsStore.removeAtIndex(index)" />
                  <q-btn size="sm" flat icon="delete" @click="currentPlayListsStore.remove(playList.id)" />
                  <q-btn size="sm" flat icon="close" @click="currentPlayListsStore.closeAtIndex(index)" />
                </q-toolbar>
              </div>
            </div>
          </div>
        </q-tab>
      </q-tabs>

      <q-tab-panels v-model="currentPlayListTab">
        <q-tab-panel :name="playList.id" v-for="playList, index2 in currentPlayListsStore.playLists" :key="playList.id">
          <q-markup-table dense flat bordered separator="cell">
            <thead>
              <tr>
                <th v-for="column in visibleColumns" :key="column.name">{{ column.label }}</th>
              </tr>
            </thead>
            <tbody v-if="currentPlayListsStore.playLists[index2]?.items.length ?? 0 > 0" @click="handleTableBodyClick">
              <tr class="cursor-pointer" v-for="item, index in currentPlayListsStore.playLists[index2]?.items"
                :key="index">
                <td v-if="visibleColumnNames.includes('index')" class="text-right"><q-icon name="play_arrow" size="sm"
                    color="pink" class="cursor-pointer" v-if="index == currentPlayListStore.currentItemIndex" /> {{
                      index +
                      1 }}/{{
                    currentPlayListsStore.playLists[index2]?.items.length }}</td>
                <td style="padding: 0px !important; width: 4em !important;" v-if="visibleColumnNames.includes('image')">
                  <TrackImage :src="item.file?.trackInfo.imageURL.small ?? null" round
                    :rotate="index == currentPlayListStore.currentItemIndex" />
                </td>
                <td v-if="visibleColumnNames.includes('trackTitle')">{{ item.file?.trackInfo.title }}</td>
                <td v-if="visibleColumnNames.includes('trackArtist')">{{ item.file?.trackInfo.artist.name }}</td>
                <td v-if="visibleColumnNames.includes('trackAlbumTitle')">{{ item.file?.trackInfo.album.title }}</td>
                <td v-if="visibleColumnNames.includes('trackAlbumArtist')">{{ item.file?.trackInfo.album.artist.name }}
                </td>
                <td v-if="visibleColumnNames.includes('year')">{{ item.file?.trackInfo.album.year }}</td>
                <td v-if="visibleColumnNames.includes('trackNumber')">0</td>
                <td v-if="visibleColumnNames.includes('actions')">
                  <q-btn-group outline>
                    <q-btn size="sm" icon="north" title="Up" data-button-action="up" />
                    <q-btn size="sm" icon="south" title="Down" data-button-action="down" />
                    <q-btn size="sm" icon="delete" title="Remove" data-button-action="remove" />
                    <q-btn size="sm" icon="favorite" title="Toggle favorite" data-button-action="toggleFavorite" />
                    <q-btn size="sm" icon="save_alt" title="Download" />
                  </q-btn-group>
                </td>
              </tr>
            </tbody>
          </q-markup-table>
        </q-tab-panel>
      </q-tab-panels>


    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from "vue";
//import { useI18n } from "vue-i18n";
import { default as BreadCrumb } from "src/components/BreadCrumb.vue";
import { default as TrackImage } from "src/components/TrackImage.vue";
import { useCurrentPlayListStore } from "src/stores/currentPlayList";
import { useCurrentPlaylistItemStore } from "src/stores/currentPlaylistItem";
import { usePlayerStore } from "src/stores/player";
import { useCurrentPlayListsStore } from "src/stores/currentPlayLists";
import { uid } from "quasar";

//const { t } = useI18n();

const currentPlayListsStore = useCurrentPlayListsStore();



const currentPlayListTab = ref<string | null>(null);

currentPlayListTab.value = currentPlayListsStore.playLists[0]?.id ?? null;

interface Column {
  name: string;
  label: string;
  index: number;
  visible: boolean;
};

const availableColumns = reactive<Column[]>(
  [
    {
      name: "index",
      label: "Index",
      index: 0,
      visible: true,
    },
    {
      name: "image",
      label: "Image",
      index: 1,
      visible: true,
    },
    {
      name: "trackTitle",
      label: "Title",
      index: 2,
      visible: true,
    },
    {
      name: "trackArtist",
      label: "Artist",
      index: 3,
      visible: true,
    },
    {
      name: "trackAlbumTitle",
      label: "Album",
      index: 4,
      visible: true,
    },
    {
      name: "trackAlbumArtist",
      label: "Album artist",
      index: 5,
      visible: true,
    },
    {
      name: "year",
      label: "Year",
      index: 6,
      visible: true,
    },
    {
      name: "actions",
      label: "Actions",
      index: 7,
      visible: true,
    }
  ]
);

const visibleColumns = computed(() => availableColumns.filter((column) => column.visible));
const visibleColumnNames = computed(() => visibleColumns.value.map((column) => column.name));
const currentPlayListStore = useCurrentPlayListStore();
const currentPlaylistItemStore = useCurrentPlaylistItemStore();
const playerStore = usePlayerStore();


const handleTableBodyClick = (event: MouseEvent) => {
  const target = event.target;
  if (!(target instanceof HTMLElement)) return;
  const btn = target.closest("[data-button-action]");
  if (btn instanceof HTMLElement) {
    console.log("button action:", btn.dataset.buttonAction);
  } else {
    const row = target.closest("tr");
    if (!(row instanceof HTMLTableRowElement)) return;
    const rowIndex = row.sectionRowIndex;
    console.log("row index", rowIndex);

    console.log(currentPlayListStore.playList.items[rowIndex]!.file);
    currentPlaylistItemStore.setTrack(
      currentPlayListStore.playList.items[rowIndex]!.file!.id,
      currentPlayListStore.playList.items[rowIndex]!.file!.name,
      currentPlayListStore.playList.items[rowIndex]!.file!.size,
      currentPlayListStore.playList.items[rowIndex]!.file!.mime,
      currentPlayListStore.playList.items[rowIndex]!.file!.trackInfo.playTimeSeconds,
      currentPlayListStore.playList.items[rowIndex]!.file!.trackInfo.title,
      currentPlayListStore.playList.items[rowIndex]!.file!.trackInfo.artist.name,
      currentPlayListStore.playList.items[rowIndex]!.file!.trackInfo.artist.mbId,
      currentPlayListStore.playList.items[rowIndex]!.file!.trackInfo.album.title,
      currentPlayListStore.playList.items[rowIndex]!.file!.trackInfo.album.mbId,
      currentPlayListStore.playList.items[rowIndex]!.file!.trackInfo.album.year,
      currentPlayListStore.playList.items[rowIndex]!.file!.trackInfo.album.artist.name,
      currentPlayListStore.playList.items[rowIndex]!.file!.trackInfo.album.artist.mbId,
      currentPlayListStore.playList.items[rowIndex]!.file!.trackInfo.image ? currentPlayListStore.playList.items[rowIndex]!.file!.trackInfo.image.small : null,
      currentPlayListStore.playList.items[rowIndex]!.file!.trackInfo.image ? currentPlayListStore.playList.items[rowIndex]!.file!.trackInfo.image.normal : null,
      currentPlayListStore.playList.items[rowIndex]!.file!.trackInfo.favorited
    );
    currentPlayListStore.currentItemIndex = rowIndex;
    playerStore.interact();
    playerStore.play(true);

    //const rowData = currentPlayListStore.playList.items[rowIndex];
    //console.log("Fila clickeada:", rowData);
  }
};

const onNew = async (): Promise<void> => {
  console.log("onNew");
  const PlayListId = uid();
  try {
    await currentPlayListsStore.add(PlayListId, `New playlist ${currentPlayListsStore.playLists.length + 1}`);
  } catch (e) {
    console.error(e);
  }
  currentPlayListTab.value = PlayListId;
};

const onEmpty = (): void => {
  currentPlayListStore.empty();
};

const onDiscover = (): void => {
  console.log("onDiscover");
  currentPlayListStore.init().then((successResponse) => {
    /*
    currentPlayListStore.playList.items = successResponse.data.playList.items.map(
      (i: any) => {
        i.trackInfo.image = i.trackInfo.imageURL;
        return ({ file: i });
      }
    );
    */
    console.log(successResponse);
  }).catch((errorResponse) => { console.error(errorResponse); }).finally(() => { });
};

const onRandomize = (): void => {
  console.log("onRandomize");
};

const onSkipPrevious = (): void => {
  console.log("onSkipPrevious");
};

const onPlay = (): void => {
  console.log("onPlay");
};

const onPause = (): void => {
  console.log("onPause");
};

const onStop = (): void => {
  console.log("onStop");
};

const onSkipNext = (): void => {
  console.log("onSkipNext");
};

const onToggleColumnVisibility = (column: Column): void => {
  column.visible = !column.visible;
};

</script>

<style lang="css"></style>