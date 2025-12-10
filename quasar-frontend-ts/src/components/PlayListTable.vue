<template>
  <q-markup-table dense flat bordered separator="cell">
    <thead>
      <tr>
        <th v-for="column in visibleColumns" :key="column.name">{{ column.label }}</th>
      </tr>
    </thead>
    <tbody v-if="playListHasItems" @click="handleTableBodyClick">
      <tr class="cursor-pointer" v-for="item, itemIndex in playList.items" :key="item._id">
        <td v-if="visibleColumnsNames.includes('index')" class="text-right">
          <q-icon name="play_arrow" size="sm" color="pink" class="cursor-pointer"
            v-if="active && itemIndex == playListCurrentItemIndex" />
          {{ itemIndex + 1 }}/{{ playListItemCount }}
        </td>
        <td class="playlist-column-image" v-if="visibleColumnsNames.includes('image')">
          <TrackImage :src="item.images?.small ?? null" :round="roundImage" :rotate="roundImage && rotateImage" />
        </td>
        <!-- TODO: stream combine columns with colspan ??? -->
        <td v-if="columnTrackTitleVisible">{{ item.file?.trackInfo.title }}</td>
        <td v-if="columnTrackArtistVisible">{{ item.file?.trackInfo.artist.name }}</td>
        <td v-if="columnTrackAlbumTitleVisible">{{ item.file?.trackInfo.album.title }}</td>
        <td v-if="columnTrackAlbumArtistVisible">{{ item.file?.trackInfo.album.artist.name }}
        </td>
        <td v-if="columnTrackYearVisible">{{ item.file?.trackInfo.album.year }}</td>
        <td v-if="columnTrackAlbumTrackIndexVisible">0</td>
        <td v-if="columnTrackActionsVisible">
          <q-btn-group outline>
            <q-btn size="sm" icon="north" title="Up" data-button-action="up" :disable="itemIndex === 0" />
            <q-btn size="sm" icon="south" title="Down" data-button-action="down"
              :disable="itemIndex === playList.items.length - 1" />
            <q-btn size="sm" icon="delete" title="Remove" data-button-action="remove" />
            <q-btn size="sm" icon="favorite" :class="{ 'text-pink': item.file?.trackInfo.favorited }"
              title="Toggle favorite" data-button-action="toggleFavorite" />
            <q-btn size="sm" icon="save_alt" title="Download"
              :href="item.file !== null ? buildDownloadTrackURL(item.file?.id) : '#'" />
          </q-btn-group>
        </td>
      </tr>
    </tbody>
  </q-markup-table>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { type PlayListTableColumn } from "src/types/common";
import { type PlayListClass } from "src/types/playList";
import { default as TrackImage } from "src/components/TrackImage.vue";
import { buildDownloadTrackURL } from "src/composables/common";

interface PlayListTableProps {
  disable?: boolean;
  columns: PlayListTableColumn[];
  playList: PlayListClass;
  active: boolean;
  playListCurrentItemIndex: number;
  roundImage?: boolean;
  rotateImage?: boolean;
};

const props = withDefaults(defineProps<PlayListTableProps>(), {
  disable: false,
  roundImage: false,
  rotateImage: false,
});

const emit = defineEmits(['onClickItemAtIndex', 'onActionMoveUpItemAtIndex', 'onActionMoveDownItemAtIndex', 'onActionRemoveItemAtIndex', 'onActionToggleFavoriteItemAtIndex']);

const visibleColumns = computed(() => props.columns.filter((column) => column.visible));

const visibleColumnsNames = computed(() => visibleColumns.value.map((column) => column.name));

const playListItemCount = computed(() => props.playList.items.length);

const playListHasItems = computed(() => playListItemCount.value > 0);

const columnTrackTitleVisible = computed(() => visibleColumnsNames.value.includes('trackTitle'));
const columnTrackArtistVisible = computed(() => visibleColumnsNames.value.includes('trackArtist'));
const columnTrackAlbumTitleVisible = computed(() => visibleColumnsNames.value.includes('trackAlbumTitle'));
const columnTrackAlbumArtistVisible = computed(() => visibleColumnsNames.value.includes('trackAlbumArtist'));
const columnTrackYearVisible = computed(() => visibleColumnsNames.value.includes('year'));
const columnTrackAlbumTrackIndexVisible = computed(() => visibleColumnsNames.value.includes('trackAlbumNumber'));
const columnTrackActionsVisible = computed(() => visibleColumnsNames.value.includes('actions'));

const handleTableBodyClick = (event: MouseEvent) => {
  const target = event.target;
  if (!(target instanceof HTMLElement)) return;
  const row = target.closest("tr");
  if (!(row instanceof HTMLTableRowElement)) return;
  const rowIndex = row.sectionRowIndex;
  const btn = target.closest("[data-button-action]");
  if (btn instanceof HTMLElement) {
    switch (btn.dataset.buttonAction) {
      case "up":
        emit("onActionMoveUpItemAtIndex", rowIndex);
        break;
      case "down":
        emit("onActionMoveDownItemAtIndex", rowIndex);
        break;
      case "remove":
        emit("onActionRemoveItemAtIndex", rowIndex);
        break;
      case "toggleFavorite":
        emit("onActionToggleFavoriteItemAtIndex", rowIndex);
        break;
    }
  } else {
    emit("onClickItemAtIndex", rowIndex);
  }
};

</script>

<style lang="css">
td.playlist-column-image {
  padding: 0px !important;
  width: 4em;
}
</style>