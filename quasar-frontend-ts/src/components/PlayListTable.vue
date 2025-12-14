<template>
  <q-markup-table dense flat bordered separator="cell">
    <thead>
      <tr>
        <th v-for="column in playListVisibleColumnsStore.visibleColumnDefinitions" :key="column.name">{{ column.label }}
        </th>
      </tr>
    </thead>
    <tbody v-if="playListHasItems" @click="handleTableBodyClick">
      <tr class="cursor-pointer" v-for="item, itemIndex in playList.items" :key="item._id">
        <td v-if="playListVisibleColumnsStore.isIndexColumnVisible" class="text-right">
          <q-icon name="play_arrow" size="sm" color="pink" class="cursor-pointer"
            v-if="active && itemIndex == playListCurrentItemIndex" />
          {{ itemIndex + 1 }}/{{ playListItemCount }}
        </td>
        <td class="playlist-column-image" v-if="playListVisibleColumnsStore.isImageColumnVisible">
          <!--
          <StaticAlbumCoverImage :image="item.images?.small ?? null" />
          -->
          <TrackImage :src="item.images?.small ?? null" :round="roundImage" :rotate="roundImage && rotateImage" />
        </td>
        <!-- TODO: stream combine columns with colspan ??? -->
        <td v-if="playListVisibleColumnsStore.isTrackTitleColumnVisible">{{ item.file?.trackInfo.title }}</td>
        <td v-if="playListVisibleColumnsStore.isTrackArtistColumnVisible">{{ item.file?.trackInfo.artist.name }}</td>
        <td v-if="playListVisibleColumnsStore.isAlbumTitleColumnVisible">{{ item.file?.trackInfo.album.title }}
        </td>
        <td v-if="playListVisibleColumnsStore.isAlbumTrackNumberColumnVisible">0</td>
        <td v-if="playListVisibleColumnsStore.isAlbumArtistColumnVisible">{{ item.file?.trackInfo.album.artist.name
        }}
        </td>
        <td v-if="playListVisibleColumnsStore.isYearColumnVisible">{{ item.file?.trackInfo.album.year }}</td>
        <td v-if="playListVisibleColumnsStore.isActionsColumnVisible">
          <q-btn-group outline>
            <q-btn class="q-pa-xs" size="sm" icon="north" title="Up" data-button-action="up"
              :disable="itemIndex === 0" />
            <q-btn class="q-pa-xs" size="sm" icon="south" title="Down" data-button-action="down"
              :disable="itemIndex === playList.items.length - 1" />
            <q-btn class="q-pa-xs" size="sm" icon="delete" title="Remove" data-button-action="remove" />
            <q-btn class="q-pa-xs" size="sm" icon="favorite" :class="{ 'text-pink': item.file?.trackInfo.favorited }"
              title="Toggle favorite" data-button-action="toggleFavorite" />
            <q-btn class="q-pa-xs" size="sm" icon="save_alt" title="Download"
              :href="item.file !== null ? buildDownloadTrackURL(item.file?.id) : '#'" />
          </q-btn-group>
        </td>
      </tr>
    </tbody>
  </q-markup-table>
</template>
<script setup lang="ts">
  import { computed } from "vue";
  import { type PlayList } from "src/types/playList";
  import { default as TrackImage } from "src/components/TrackImage.vue";
  //import { default as StaticAlbumCoverImage } from "src/components/Widgets/Visualizations/StaticAlbumCoverImage.vue";
  import { buildDownloadTrackURL } from "src/composables/common";
  import { usePlayListVisibleColumnsStore } from "src/stores/playListVisibleColumns";

  interface PlayListTableProps {
    disable?: boolean;
    playList: PlayList;
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

  const playListVisibleColumnsStore = usePlayListVisibleColumnsStore();

  const emit = defineEmits(['onClickItemAtIndex', 'onActionMoveUpItemAtIndex', 'onActionMoveDownItemAtIndex', 'onActionRemoveItemAtIndex', 'onActionToggleFavoriteItemAtIndex']);

  const playListItemCount = computed(() => props.playList.items.length);

  const playListHasItems = computed(() => playListItemCount.value > 0);


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
