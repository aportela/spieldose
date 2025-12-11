import { defineStore, acceptHMRUpdate } from 'pinia';
import { api } from 'src/composables/api';
import { createStorageEntry } from 'src/composables/localStorage';
import { PlayListClass, type PlayList, type PlayListItemClass } from 'src/types/playList';
import { type AddPlayListResponse } from 'src/types/apiResponses';
import { type Player, type PlayerStatus, type PlayerRepeatMode } from 'src/types/common';

const localStorageAudioVolume = createStorageEntry<number>('audio.volume', 1);
const localStorageAudioMuted = createStorageEntry<boolean>('audio.muted', false);
const localStoragePlayerRepeatMode = createStorageEntry<PlayerRepeatMode>(
  'player.repeatMode',
  'none',
);
const localStoragePlayerShuffle = createStorageEntry<boolean>('player.shuffle', false);

interface State {
  audio: {
    instance: HTMLAudioElement;
    volume: number;
    muted: boolean;
    currentTime: number;
    duration: number;
  };
  player: Player;
  selectedPlayListIndex: number;
  activePlayListIndex: number;
  activePlayListItemIndex: number;
  playLists: PlayList[];
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
    selectedPlayListIndex: 0,
    activePlayListIndex: 0,
    activePlayListItemIndex: 0,
    playLists: [],
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
    activePlayList: (state: State): PlayList | null =>
      state.playLists.length > 0 ? state.playLists[state.activePlayListIndex]! : null,
    currentFileId: (state: State): string | null =>
      state.playLists[state.activePlayListIndex]?.items[state.activePlayListItemIndex]?.file?.id ??
      null,
    currentActivePlayListItem: (state: State): PlayListItemClass | null =>
      state.playLists[state.activePlayListIndex]?.items[state.activePlayListItemIndex] ?? null,
    /*
      active/current playlist property getters
    */
    allowSkipPreviousItemOnActivePlayList: (state: State) => state.activePlayListItemIndex > 0,
    allowSkipNextItemOnActivePlayList: (state: State) =>
      state.activePlayListItemIndex <
      (state.playLists[state.activePlayListIndex]?.items.length ?? 0) - 1,
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
        if (this.allowSkipNextItemOnActivePlayList) {
          this.skipNextItemOnActivePlayList();
        }
      });
      this.audio.instance.addEventListener('timeupdate', () => {
        this.audio.currentTime = this.audio.instance.currentTime;
      });
      this.audio.instance.addEventListener('error', (event: Event) => {
        console.error('create - audio event error', event);
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
    setAudioSource(src: string) {
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
      localStorageAudioMuted.set(this.audio.instance.muted);
    },
    toggleAudioMute: function (): void {
      this.audio.instance.muted = !this.audio.instance.muted;
      localStorageAudioMuted.set(this.audio.instance.muted);
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
    skipPreviousItemOnActivePlayList(): boolean {
      if (this.activePlayListItemIndex > 0) {
        if (!this.playerHasPreviousUserInteractions) {
          this.playerInteract();
        } else {
          if (!this.playerIsStopped) {
            this.playerActionStop();
          }
        }
        this.activePlayListItemIndex--;
        this.playerActionPlay(true);
        return true;
      } else {
        console.error(
          'skipPreviousItemOnActivePlayList - invalid activePlayListItemIndex',
          this.activePlayListItemIndex,
        );
        return false;
      }
    },
    skipNextItemOnActivePlayList(): boolean {
      if (
        this.activePlayListItemIndex < (this.playLists[this.activePlayListIndex]?.items.length ?? 0)
      ) {
        if (!this.playerHasPreviousUserInteractions) {
          this.playerInteract();
        } else {
          if (!this.playerIsStopped) {
            this.playerActionStop();
          }
        }
        this.activePlayListItemIndex++;
        this.playerActionPlay(true);
        return true;
      } else {
        console.error(
          'skipNextItemOnActivePlayList - invalid activePlayListItemIndex',
          this.activePlayListItemIndex,
        );
        return false;
      }
    },
    setActivePlayListIndex(index: number): boolean {
      if (index > 0 && index < this.playLists.length) {
        this.activePlayListIndex = index;
        return true;
      } else {
        console.error('setActivePlayListIndex - invalid index', index);
        return false;
      }
    },
    setSelectedPlayListId(id: string): boolean {
      const index = this.playLists.findIndex((playList) => playList.id === id);
      if (index !== -1) {
        this.selectedPlayListIndex = index;
        return true;
      } else {
        console.error('setSelectedPlayListId - missing index for id', id);
        return false;
      }
    },
    setActivePlayListId(id: string): boolean {
      const index = this.playLists.findIndex((playList) => playList.id === id);
      if (index !== -1) {
        this.activePlayListIndex = index;
        return true;
      } else {
        console.error('setActivePlayListId - missing index for id', id);
        return false;
      }
    },
    async init() {
      const response = await api.playList.getCurrentPlayLists();
      this.playLists = response.data.playLists.map(
        (playList: PlayList) => new PlayListClass(playList.id, playList.name, playList.items),
      );
    },
    async add(id: string, name: string) {
      const PlayList: AddPlayListResponse = await api.playList.add(id, name);
      this.playLists.push({
        id: PlayList.data.playList.id,
        name: PlayList.data.playList.name,
        items: PlayList.data.playList.items,
      });
      this.selectedPlayListIndex = this.playLists.length - 1;
    },
    async remove(playListId: string) {
      console.log('remove', playListId);
      await api.playList.remove(playListId);
      this.playLists = this.playLists.filter((playList) => playList.id !== playListId);
      this.selectedPlayListIndex = 0;
      // TODO: stop if remove active
    },
    async randomFill(playListId: string) {
      console.log('randomFill', playListId);
      const index = this.playLists.findIndex((playList) => playList.id === playListId);
      if (index !== -1) {
        const filledPlayList = await api.playList.randomFill(playListId);
        this.playLists[index] = filledPlayList.data.playList;
        return true;
      } else {
        console.error('empty - missing index for id', playListId);
        return false;
      }
    },
    empty(playListId: string): boolean {
      console.log('empty', playListId);
      const index = this.playLists.findIndex((playList) => playList.id === playListId);
      if (index !== -1) {
        this.playLists[index]!.items.length = 0;
        return true;
      } else {
        console.error('empty - missing index for id', playListId);
        return false;
      }
    },
    closePlayListAtIndex(playListIndex: number) {
      console.log('closePlayListAtIndex', playListIndex);
      this.playLists.splice(playListIndex, 1);
      this.selectedPlayListIndex = 0;
      // TODO: stop if remove active
    },
    savePlayListAtIndex(playListIndex: number) {
      console.log('savePlayListAtIndex', playListIndex);
    },
    async removePlayListAtIndex(playListIndex: number) {
      console.log('removePlayListAtIndex', playListIndex);
      await api.playList.remove(this.playLists[playListIndex]!.id);
      this.playLists = this.playLists.filter(
        (playList) => playList.id !== this.playLists[playListIndex]!.id,
      );
      this.playLists.splice(playListIndex, 1);
      this.activePlayListIndex = 0;
    },
    selectPlayListItem(playListIndex: number, playListItemIndex: number): boolean {
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
        this.activePlayListIndex = playListIndex;
        this.activePlayListItemIndex = playListItemIndex;
        this.playerActionPlay(true);
        return true;
      } else {
        console.error('selectPlayListItem - invalid playListItemIndex', playListItemIndex);
        return false;
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
    toggleFavoritePlayListItem(playListIndex: number, playListItemIndex: number): boolean {
      console.log('toggleFavoritePlayListItem', playListIndex, playListItemIndex);
      return false;
    },
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useCurrentPlayListsStore, import.meta.hot));
}
