import { defineStore, acceptHMRUpdate } from 'pinia';
import { AxiosError } from 'axios';
import { createStorageEntry } from 'src/composables/localStorage';
import { api } from 'src/composables/api';
import { type GetNewAccessTokenResponse as GetNewAccessTokenResponseInterface } from 'src/types/apiResponses';

const localStorageShowToolTips = createStorageEntry<boolean>('session.showToolTips', true);

interface State {
  session: {
    accessToken: {
      token: string | null;
      expiresAtTimestamp: number | null;
    };
    other: { showToolTips: boolean };
  };
}

export const useSessionStore = defineStore('sessionStore', {
  state: (): State => ({
    session: {
      accessToken: {
        token: null,
        expiresAtTimestamp: null,
      },
      other: {
        showToolTips: localStorageShowToolTips.get(),
      },
    },
  }),
  getters: {
    hasAccessToken: (state: State): boolean =>
      state.session.accessToken.token !== null &&
      state.session.accessToken.expiresAtTimestamp !== null,
    accessToken: (state: State): string | null => state.session.accessToken.token,
    accessTokenExpirationTimestamp: (state): number | null =>
      state.session.accessToken.expiresAtTimestamp,
    showToolTips: (state): boolean => state.session.other.showToolTips,
  },
  actions: {
    async refreshAccessToken(): Promise<boolean> {
      try {
        const successResponse: GetNewAccessTokenResponseInterface =
          await api.auth.renewAccessToken();
        this.setAccessToken(
          successResponse.data.accessToken.token,
          successResponse.data.accessToken.expiresAtTimestamp,
        );
        return true;
      } catch (e: unknown) {
        if (e instanceof AxiosError) {
          // TODO: replace with e.response?.status ???
          if (e.status !== 401) {
            // 401 is the "normal" error response if we do not have a previous refresh token
            console.error('Invalid error http response code', e.status);
          }
        } else {
          console.error('Invalid error response', e);
        }
        return false;
      }
    },
    accessTokenExpiresBeforeInterval(interval: number) {
      if (this.session.accessToken.expiresAtTimestamp) {
        const currentTimestamp = Math.round(Date.now() / 1000);
        return this.session.accessToken.expiresAtTimestamp - currentTimestamp <= interval;
      } else {
        return false;
      }
    },
    setAccessToken(token: string, expiresAtTimestamp: number): void {
      this.session.accessToken.token = token;
      this.session.accessToken.expiresAtTimestamp = expiresAtTimestamp;
    },
    removeAccessToken(): void {
      this.session.accessToken.token = null;
      this.session.accessToken.expiresAtTimestamp = null;
    },
    toggleToolTips(enabled: boolean) {
      this.session.other.showToolTips = enabled;
      localStorageShowToolTips.set(this.session.other.showToolTips);
    },
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useSessionStore, import.meta.hot));
}
