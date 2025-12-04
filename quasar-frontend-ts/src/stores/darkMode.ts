import { defineStore, acceptHMRUpdate } from 'pinia';
import { Dark } from "quasar";
import { darkMode as localStorageDarkMode } from "src/composables/localStorage";

const savedMode = localStorageDarkMode.get();

if (savedMode === true) {
  Dark.set(true);
} else if (savedMode === false) {
  Dark.set(false);
} else {
  Dark.set("auto");
}

interface State {
  active: boolean;
};

export const useDarkModeStore = defineStore('darkModeStore', {
  state: (): State => ({
    active: Dark.isActive
  }),
  getters: {
    isActive(state): boolean {
      return state.active
    },
  },
  actions: {
    set(active: boolean): void {
      this.active = active;
      Dark.set(this.active);
      localStorageDarkMode.set(this.active);
    },
    toggle(): void {
      this.set(!this.active);
    }
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useDarkModeStore, import.meta.hot));
}
