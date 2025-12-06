import { defineStore, acceptHMRUpdate } from 'pinia';
import { createStorageEntry } from 'src/composables/localStorage';

const localStorageEmail = createStorageEntry<string | null>("profile.lastEmailUsed", "foo@ba.r");

interface State {
  profile: {
    lastEmailUsed: string | null;
  }
};

export const useProfileStore = defineStore('profileStore', {
  state: (): State => ({
    profile: {
      lastEmailUsed: localStorageEmail.get(),
    },
  }),
  getters: {
    lastEmailUsed: (state) => state.profile.lastEmailUsed,
  },
  actions: {
    setLastEmailUsed(email: string | null) {
      this.profile.lastEmailUsed = email;
      localStorageEmail.set(this.profile.lastEmailUsed);
    }
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useProfileStore, import.meta.hot));
}
