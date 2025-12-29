import { defineStore, acceptHMRUpdate } from 'pinia';
import { api } from 'src/composables/api';
import { createStorageEntry } from 'src/composables/localStorage';
import { type PlayList, type PlayListItemClass } from 'src/types/playList';
import {
  type AddPlayListResponse,
  type GetCurrentPlayListsResponse,
  type RandomPlayListFillResponse,
} from 'src/types/apiResponses';
import { type Player, type PlayerStatus, type PlayerRepeatMode } from 'src/types/common';

const localStorageAudioVolume = createStorageEntry<number>('audio.volume', 1);
const localStorageAudioMuted = createStorageEntry<boolean>('audio.muted', false);
const localStoragePlayerRepeatMode = createStorageEntry<PlayerRepeatMode>(
  'player.repeatMode',
  'none',
);
const localStoragePlayerShuffle = createStorageEntry<boolean>('player.shuffle', false);

const getFileURL = (fileId: string | null): string | null => {
  if (fileId) {
    return '/api2/file/' + fileId + '/raw';
  } else {
    return null;
  }
};

interface State {
  audio: {
    instance: HTMLAudioElement;
    volume: number;
    muted: boolean;
    currentTime: number;
    duration: number;
  };
  player: Player;
  playLists: PlayList[];
  currentActivePlayList: {
    id: string | null;
    index: number | null;
    itemIndex: number | null;
  };
  currentSelectedPlayList: {
    id: string | null;
    index: number | null;
  };
  processing: boolean;
}

export const useCurrentPlayListsStore = defineStore('currentPlayListsStore', {
  state: (): State => ({
    audio: {
      instance: new Audio(),
      volume: localStorageAudioVolume.get(),
      muted: localStorageAudioMuted.get(),
      currentTime: 0,
      duration: 0,
    },
    player: {
      userInteracted: false,
      status: 'stopped',
      repeatMode: 'none',
      shuffle: false,
    },
    playLists: [],
    currentActivePlayList: {
      id: null,
      index: null,
      itemIndex: null,
    },
    currentSelectedPlayList: {
      id: null,
      index: null,
    },
    processing: false,
  }),
  getters: {
    /* audio */
    audioInstance: (state: State): HTMLAudioElement => state.audio.instance,
    audioVolume: (state: State): number => state.audio.volume,
    audioMuted: (state: State): boolean => state.audio.muted,
    audioCurrentTime: (state: State): number => state.audio.currentTime,
    audioDuration: (state: State): number => state.audio.duration,

    /* audio */

    /* player */
    playerHasPreviousUserInteractions: (state: State): boolean => state.player.userInteracted,
    playerStatus: (state: State): PlayerStatus => state.player.status,
    playerRepeatMode: (state: State): PlayerRepeatMode => state.player.repeatMode,
    playerShuffle: (state: State): boolean => state.player.shuffle,

    playerIsPlaying: (state: State): boolean => state.player.status === 'playing',
    playerIsPaused: (state: State): boolean => state.player.status === 'paused',
    playerIsStopped: (state: State): boolean => state.player.status === 'stopped',
    /* player */

    hasPlayLists: (state: State): boolean => state.playLists.length > 0,
    hasActivePlayList: (state: State): boolean =>
      state.currentActivePlayList.id !== null &&
      state.currentActivePlayList.index !== null &&
      state.currentActivePlayList.itemIndex !== null,
    activePlayList: (state: State): PlayList | null =>
      state.playLists.length > 0 &&
      state.currentActivePlayList.index !== null &&
      state.currentActivePlayList.index >= 0 &&
      state.currentActivePlayList.index < state.playLists.length
        ? state.playLists[state.currentActivePlayList.index]!
        : null,
    currentFileId: (state: State): string | null =>
      state.processing === false &&
      state.playLists.length > 0 &&
      state.currentActivePlayList.index !== null &&
      state.currentActivePlayList.index >= 0 &&
      state.currentActivePlayList.index < state.playLists.length &&
      state.currentActivePlayList.itemIndex !== null &&
      state.currentActivePlayList.itemIndex >= 0
        ? state.playLists[state.currentActivePlayList.index]!.items[
            state.currentActivePlayList.itemIndex
          ]!.file!.id
        : null,
    currentFileURL: (state: State): string | null =>
      state.processing === false &&
      state.playLists.length > 0 &&
      state.currentActivePlayList.index !== null &&
      state.currentActivePlayList.index >= 0 &&
      state.currentActivePlayList.index < state.playLists.length &&
      state.currentActivePlayList.itemIndex !== null &&
      state.currentActivePlayList.itemIndex >= 0
        ? getFileURL(
            state.playLists[state.currentActivePlayList.index]!.items[
              state.currentActivePlayList.itemIndex
            ]!.file!.id,
          )
        : null,
    currentActivePlayListItem: (state: State): PlayListItemClass | null =>
      state.processing === false &&
      state.playLists.length > 0 &&
      state.currentActivePlayList.index !== null &&
      state.currentActivePlayList.index >= 0 &&
      state.currentActivePlayList.index < state.playLists.length &&
      state.currentActivePlayList.itemIndex !== null &&
      state.currentActivePlayList.itemIndex >= 0
        ? state.playLists[state.currentActivePlayList.index]!.items[
            state.currentActivePlayList.itemIndex
          ]!
        : null,
    allowSkipPreviousItemOnActivePlayList: (state: State) =>
      state.currentActivePlayList.index !== null && state.currentActivePlayList.index > 0,
    allowSkipNextItemOnActivePlayList: (state: State) =>
      state.currentActivePlayList.index !== null &&
      state.currentActivePlayList.index <
        (state.playLists[state.currentActivePlayList.index]?.items.length ?? 0) - 1,
  },
  actions: {
    // constructor / destructor
    create: function (src?: string | null): void {
      this.audio.instance.autoplay = false;
      if (src) {
        this.audio.instance.src = src;
      }
      this.setAudioVolume(this.audio.volume);
      this.setAudioMute(this.audio.muted);
      // required for radio stations streams
      this.audio.instance.crossOrigin = 'anonymous';
      this.audio.instance.addEventListener('loadedmetadata', () => {
        this.audio.duration = this.audio.instance.duration;
      });
      this.audio.instance.addEventListener('ended', () => {
        this.skipNextItemOnActivePlayList();
      });
      this.audio.instance.addEventListener('timeupdate', () => {
        this.audio.currentTime = this.audio.instance.currentTime;
      });
      this.audio.instance.addEventListener('error', (event: Event) => {
        console.error('create - audio event error', event);
        // TODO: skip next item ???
      });
    },
    destroy: function (): void {
      this.audio.instance.removeEventListener('loadedmetadata', () => {});
      this.audio.instance.removeEventListener('ended', () => {});
      this.audio.instance.removeEventListener('timeupdate', () => {});
      this.audio.instance.removeEventListener('error', () => {});
    },
    // constructor / destructor

    // audio block
    setAudioSource(src: string): void {
      this.audio.instance.src = src;
      if (this.playerHasPreviousUserInteractions && !this.playerIsPlaying) {
        this.playerActionPlay(true);
      }
    },
    setAudioVolume: function (volume: number): boolean {
      if (volume >= 0 && volume <= 1) {
        this.audio.instance.volume = volume;
        localStorageAudioVolume.set(this.audio.instance.volume);
        return true;
      } else {
        return false;
      }
    },
    setAudioMute: function (muted: boolean): void {
      this.audio.instance.muted = muted;
      this.audio.muted = muted;
      localStorageAudioMuted.set(this.audio.muted);
    },
    toggleAudioMute: function (): void {
      this.setAudioMute(!this.audio.muted);
    },
    setAudioCurrentTime: function (time: number | null): boolean {
      if (time !== null && time > 0 && time <= this.audio.instance.duration) {
        this.audio.instance.currentTime = time;
        return true;
      } else {
        console.error('setAudioCurrentTime - invalid time', time);
        return false;
      }
    },
    // audio block

    // player block
    playerInteract: function (): void {
      this.player.userInteracted = true;
    },
    playerActionPlay: function (ignoreStatus: boolean): boolean {
      if (this.playerHasPreviousUserInteractions) {
        if (ignoreStatus) {
          this.audio.instance
            .play()
            .then(() => (this.player.status = 'playing'))
            .catch((error: Error) => {
              console.error('playerActionPlay - play promise error', error);
            });
        } else {
          if (this.playerIsPlaying) {
            this.audio.instance.pause();
            this.player.status = 'paused';
          } else if (this.playerIsPaused) {
            this.audio.instance
              .play()
              .then(() => (this.player.status = 'playing'))
              .catch((error: Error) => {
                console.error('playerActionPlay - play promise error', error);
              });
          } else {
            this.audio.instance
              .play()
              .then(() => (this.player.status = 'playing'))
              .catch((error: Error) => {
                console.error('playerActionPlay - play promise error', error);
              });
          }
        }
        return true;
      } else {
        console.error('playerActionPlay - no previous user interactions');
        return false;
      }
    },
    playerActionPause: function (): boolean {
      if (this.playerIsPlaying) {
        this.audio.instance.pause();
        this.player.status = 'paused';
        return true;
      } else {
        console.warn('playerActionPause - player not playing');
        return false;
      }
    },
    playerActionResume: function () {
      if (this.playerIsPaused) {
        this.audio.instance
          .play()
          .then(() => (this.player.status = 'playing'))
          .catch((error: Error) => {
            console.error('playerActionResume - play promise error', error);
          });
      } else {
        console.warn('playerActionResume - player not paused');
      }
    },
    playerActionStop: function (): boolean {
      if (!this.playerIsStopped) {
        this.audio.instance.pause();
        this.audio.instance.currentTime = 0;
        this.player.status = 'stopped';
        return true;
      } else {
        console.warn('playerActionStop - player already stopped');
        return false;
      }
    },
    togglePlayerRepeatMode: function (): void {
      switch (this.player.repeatMode) {
        case 'none':
          this.player.repeatMode = 'track';
          break;
        case 'track':
          this.player.repeatMode = 'playList';
          break;
        case 'playList':
          this.player.repeatMode = 'none';
          break;
      }
      localStoragePlayerRepeatMode.set(this.player.repeatMode);
    },
    togglePlayerShuffeMode: function (): void {
      this.player.shuffle = !this.player.shuffle;
      localStoragePlayerShuffle.set(this.player.shuffle);
    },
    // player block

    // playlist block
    setInternalCurrentActivePlayListItemIndex: function (itemIndex: number): boolean {
      if (itemIndex >= 0) {
        this.currentActivePlayList.itemIndex = itemIndex;
        return true;
      } else {
        return false;
      }
    },
    incrementInternalCurrentActivePlayListItemIndex: function (): boolean {
      if (this.currentActivePlayList.itemIndex !== null) {
        this.currentActivePlayList.itemIndex++;
        return true;
      } else {
        return false;
      }
    },
    decrementInternalCurrentActivePlayListItemIndex: function (): boolean {
      if (
        this.currentActivePlayList.itemIndex !== null &&
        this.currentActivePlayList.itemIndex > 0
      ) {
        this.currentActivePlayList.itemIndex--;
        return true;
      } else {
        return false;
      }
    },
    setInternalActivePlayListIndex(index: number): boolean {
      if (index > 0 && index < this.playLists.length) {
        this.currentActivePlayList.index = index;
        this.currentActivePlayList.id = this.playLists[index]!.id;
        return true;
      } else {
        return false;
      }
    },
    setInternalActivePlayListId(id: string): boolean {
      const index = this.playLists.findIndex((playList) => playList.id === id);
      if (index !== -1) {
        this.currentActivePlayList.index = index;
        this.currentActivePlayList.id = this.playLists[index]!.id;
        return true;
      } else {
        return false;
      }
    },
    unsetActivePlayList(): void {
      this.currentActivePlayList.id = null;
      this.currentActivePlayList.index = null;
      this.currentActivePlayList.itemIndex = null;
    },
    setInternalSelectedPlayListIndex(index: number): boolean {
      if (index >= 0 && index < this.playLists.length) {
        this.currentSelectedPlayList.index = index;
        this.currentSelectedPlayList.id = this.playLists[index]!.id;
        return true;
      } else {
        return false;
      }
    },
    setInternalSelectedPlayListId(id: string): boolean {
      const index = this.playLists.findIndex((playList) => playList.id === id);
      if (index !== -1) {
        this.currentSelectedPlayList.index = index;
        this.currentSelectedPlayList.id = this.playLists[index]!.id;
        return true;
      } else {
        return false;
      }
    },
    unsetSelectedPlayList(): void {
      this.currentSelectedPlayList.id = null;
      this.currentSelectedPlayList.index = null;
    },
    resetSelectedPlayList(): void {
      if (this.playLists.length > 0) {
        this.currentSelectedPlayList.id = this.playLists[0]!.id;
        this.currentSelectedPlayList.index = 0;
      } else {
        this.currentSelectedPlayList.id = null;
        this.currentSelectedPlayList.index = null;
      }
    },
    async syncActivePlayListItemIndex(): Promise<void> {
      if (this.currentActivePlayList.id !== null && this.currentActivePlayList.itemIndex !== null) {
        await api.playList.setCurrentPlayListItemIndex(
          this.currentActivePlayList.id,
          this.currentActivePlayList.itemIndex,
        );
      }
    },
    skipPreviousItemOnActivePlayList(): boolean {
      if (this.hasActivePlayList) {
        if (this.allowSkipPreviousItemOnActivePlayList) {
          if (!this.playerHasPreviousUserInteractions) {
            this.playerInteract();
          } else {
            if (!this.playerIsStopped) {
              this.playerActionStop();
            }
          }
          this.decrementInternalCurrentActivePlayListItemIndex();
          this.playerActionPlay(true);
          this.syncActivePlayListItemIndex()
            .then(() => {})
            .catch((error) => {
              console.error(error);
            })
            .finally(() => {});
          return true;
        } else {
          return false;
        }
      } else {
        return false;
      }
    },
    skipNextItemOnActivePlayList() {
      if (this.hasActivePlayList) {
        if (this.allowSkipNextItemOnActivePlayList) {
          if (!this.playerHasPreviousUserInteractions) {
            this.playerInteract();
          } else {
            if (!this.playerIsStopped) {
              this.playerActionStop();
            }
          }
          this.incrementInternalCurrentActivePlayListItemIndex();
          if (this.currentActivePlayList.index !== null) {
            this.playLists[this.currentActivePlayList.index]!.currentItemIndex =
              this.currentActivePlayList.itemIndex;
          }
          this.playerActionPlay(true);
          this.syncActivePlayListItemIndex()
            .then(() => {})
            .catch((error) => {
              console.error(error);
            })
            .finally(() => {});
          return true;
        } else {
          return false;
        }
      } else {
        return false;
      }
    },

    async init() {
      this.processing = true;
      const response: GetCurrentPlayListsResponse = await api.playList.getCurrentPlayLists();
      /*
      this.playLists = response.data.playLists.map(
        (playList: PlayList) =>
          new PlayListClass(
            playList.id,
            playList.name,
            playList.items,
            playList.flags,
            playList.currentItemIndex,
            playList.currentItemPosition,
          ),
      );
      */
      this.playLists = [...response.data.playLists];
      this.playLists.forEach((playList: PlayList, index: number) => {
        if (playList.flags.isActive) {
          this.currentActivePlayList.id = playList.id;
          this.currentActivePlayList.index = index;
          this.currentActivePlayList.itemIndex = playList.currentItemIndex;
        }
      });
      if (this.hasActivePlayList) {
        this.currentSelectedPlayList.id = this.currentActivePlayList.id;
        this.currentSelectedPlayList.index = this.currentActivePlayList.index;
      } else {
        this.resetSelectedPlayList();
      }
      this.processing = false;
    },
    async add(id: string, name: string) {
      const playList: PlayList = {
        id: id,
        name: name,
        flags: {
          isMine: true,
          isFavorites: false,
          isOpened: true,
          isActive: !this.hasPlayLists,
          isPublished: false,
          isShared: false,
        },
        items: [],
        currentItemIndex: null,
        currentItemPosition: null,
      };
      const response: AddPlayListResponse = await api.playList.add(playList);
      this.playLists.push(response.data.playList);
      if (!this.hasActivePlayList && response.data.playList.flags.isActive) {
        this.setInternalActivePlayListIndex(this.playLists.length - 1);
      }
      this.setInternalSelectedPlayListIndex(this.playLists.length - 1);
    },
    savePlayListAtIndex(index: number) {
      console.log('savePlayListAtIndex', index);
    },
    async randomFillSelectedPlayList() {
      if (this.currentSelectedPlayList.id !== null && this.currentSelectedPlayList.index !== null) {
        const response: RandomPlayListFillResponse = await api.playList.randomFill(
          this.currentSelectedPlayList.id,
        );
        this.playLists[this.currentSelectedPlayList.index] = response.data.playList;
        if (!this.hasActivePlayList) {
          this.currentActivePlayList.id = this.currentSelectedPlayList.id;
          this.currentActivePlayList.index = this.currentSelectedPlayList.index;
          this.currentActivePlayList.itemIndex = 0;
        }
      }
    },
    async emptySelectedPlayList() {
      if (this.currentSelectedPlayList.id !== null && this.currentSelectedPlayList.index !== null) {
        if (this.currentSelectedPlayList.id === this.currentActivePlayList.id) {
          this.playerActionStop();
          this.unsetActivePlayList();
        }
        await api.playList.empty(this.currentSelectedPlayList.id);
        this.playLists[this.currentSelectedPlayList.index]!.items.length = 0;
      }
    },
    async closePlayListAtIndex(index: number) {
      if (this.currentActivePlayList.index === index) {
        this.playerActionStop();
        this.unsetActivePlayList();
      }
      await api.playList.close(this.playLists[index]!.id);
      this.playLists = this.playLists.filter(
        (playList) => playList.id !== this.playLists[index]!.id,
      );
      this.resetSelectedPlayList();
    },
    async removePlayListAtIndex(index: number) {
      if (this.currentActivePlayList.index === index) {
        this.playerActionStop();
        this.unsetActivePlayList();
      }
      await api.playList.remove(this.playLists[index]!.id);
      this.playLists = this.playLists.filter(
        (playList) => playList.id !== this.playLists[index]!.id,
      );
      this.resetSelectedPlayList();
    },
    async selectPlayListItem(playListIndex: number, playListItemIndex: number) {
      console.log('selectPlayListItem', playListIndex, playListItemIndex);
      if (
        playListIndex >= 0 &&
        playListIndex < this.playLists.length &&
        playListItemIndex >= 0 &&
        playListItemIndex < this.playLists[playListIndex]!.items.length
      ) {
        if (!this.playerHasPreviousUserInteractions) {
          this.playerInteract();
        } else {
          this.playerActionStop();
        }
        this.currentSelectedPlayList.id = this.playLists[playListIndex]!.id;
        this.currentSelectedPlayList.index = playListIndex;
        this.currentActivePlayList.id = this.playLists[playListIndex]!.id;
        this.currentActivePlayList.index = playListIndex;
        this.currentActivePlayList.itemIndex = playListItemIndex;
        this.playLists[playListIndex]!.currentItemIndex = playListItemIndex;
        this.playLists[playListIndex]!.currentItemPosition = null; // TODO
        this.playLists.forEach((playList) => {
          playList.flags.isActive = false;
        });
        this.playLists[playListIndex]!.flags.isActive = true;
        this.playerActionPlay(true);
        await api.playList.setCurrentPlayListItemIndex(
          this.currentActivePlayList.id,
          this.currentActivePlayList.itemIndex,
        );
      } else {
        console.error('selectPlayListItem - invalid playListItemIndex', playListItemIndex);
      }
    },
    moveUpPlayListItem(playListIndex: number, playListItemIndex: number): boolean {
      console.log('moveUpPlayListItem', playListIndex, playListItemIndex);
      if (
        playListIndex >= 0 &&
        playListIndex < this.playLists.length &&
        playListItemIndex > 0 &&
        playListItemIndex < this.playLists[playListIndex]!.items.length
      ) {
        const [removedElement] = this.playLists[playListIndex]!.items.splice(playListItemIndex, 1);
        if (removedElement) {
          this.playLists[playListIndex]!.items.splice(playListItemIndex - 1, 0, removedElement);
          // TODO: recalc playList currentItem index && currentActivePlayList item index
          return true;
        } else {
          console.error('moveUpPlayListItem - error removing element');
          return false;
        }
      } else {
        console.error('moveUpPlayListItem - invalid playListItemIndex', playListItemIndex);
        return false;
      }
    },
    moveDownPlayListItem(playListIndex: number, playListItemIndex: number): boolean {
      console.log('moveDownPlayListItem', playListIndex, playListItemIndex);
      if (
        playListIndex >= 0 &&
        playListIndex < this.playLists.length &&
        playListItemIndex >= 0 &&
        playListItemIndex < this.playLists[playListIndex]!.items.length - 1
      ) {
        const [removedElement] = this.playLists[playListIndex]!.items.splice(playListItemIndex, 1);
        if (removedElement) {
          this.playLists[playListIndex]!.items.splice(playListItemIndex + 1, 0, removedElement);
          // TODO: recalc playList currentItem index && currentActivePlayList item index
          return true;
        } else {
          console.error('moveDownPlayListItem - error removing element');
          return false;
        }
      } else {
        console.error('moveDownPlayListItem - invalid playListItemIndex', playListItemIndex);
        return false;
      }
    },
    removePlayListItem(playListIndex: number, playListItemIndex: number): boolean {
      console.log('removePlayListItem', playListIndex, playListItemIndex);
      if (
        playListIndex >= 0 &&
        playListIndex < this.playLists.length &&
        playListItemIndex >= 0 &&
        playListItemIndex < this.playLists[playListIndex]!.items.length
      ) {
        const removedElement = this.playLists[playListIndex]!.items.splice(playListItemIndex, 1);
        if (removedElement.length > 0) {
          // TODO: recalc playList currentItem index && currentActivePlayList item index
          console.log('Item removed', removedElement);
          return true;
        } else {
          console.error('removePlayListItem - error removing element');
          return false;
        }
      } else {
        console.error('removePlayListItem - invalid playListItemIndex', playListItemIndex);
        return false;
      }
    },
    refreshInteralFavoriteValue(fileId: string, favorited: boolean) {
      this.playLists.forEach((playList: PlayList) => {
        playList.items.forEach((item: PlayListItemClass) => {
          if (item.file?.id === fileId) {
            item.file.trackInfo.favorited = favorited;
          }
        });
      });
    },
    async toggleFavoritePlayListItem(playListIndex: number, playListItemIndex: number) {
      console.log('toggleFavoritePlayListItem', playListIndex, playListItemIndex);
      try {
        if (
          this.playLists[playListIndex] &&
          this.playLists[playListIndex]?.items[playListItemIndex] &&
          this.playLists[playListIndex]?.items[playListItemIndex].file &&
          this.playLists[playListIndex]?.items[playListItemIndex]?.file.id
        ) {
          if (this.playLists[playListIndex]?.items[playListItemIndex]?.file?.trackInfo.favorited) {
            await api.track.unSetFavorite(
              this.playLists[playListIndex]?.items[playListItemIndex]?.file.id,
            );
            this.refreshInteralFavoriteValue(
              this.playLists[playListIndex]?.items[playListItemIndex]?.file.id,
              false,
            );
          } else {
            await api.track.setFavorite(
              this.playLists[playListIndex]?.items[playListItemIndex]?.file.id,
            );
            this.refreshInteralFavoriteValue(
              this.playLists[playListIndex]?.items[playListItemIndex]?.file.id,
              true,
            );
          }
        }
      } catch (e) {
        console.error(e);
      }
    },
    async toggleCurrentActivePlayListItemFavorite() {
      console.log('toggleCurrentActivePlayListItemFavorite');
      if (
        this.currentActivePlayList.index !== null &&
        this.currentActivePlayList.itemIndex !== null
      ) {
        await this.toggleFavoritePlayListItem(
          this.currentActivePlayList.index,
          this.currentActivePlayList.itemIndex,
        );
      }
    },
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useCurrentPlayListsStore, import.meta.hot));
}
