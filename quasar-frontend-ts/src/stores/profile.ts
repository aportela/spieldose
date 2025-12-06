import { defineStore, acceptHMRUpdate } from 'pinia';
import { createStorageEntry } from 'src/composables/localStorage';

const localStorageEmail = createStorageEntry<string | null>("profile.email", "foo@ba.r");

interface State {
  data: {
    email: string | null;
  }
};

export const useProfileStore = defineStore('profileStore', {
  state: (): State => ({
    data: {
      email: localStorageEmail.get(),
    },
  }),
  getters: {
    email: (state) => state.data.email,
  },
  actions: {
    setEmail(email: string | null) {
      this.data.email = email;
      localStorageEmail.set(this.data.email);
    }
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useProfileStore, import.meta.hot));
}
