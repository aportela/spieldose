import { Dark } from "quasar";

import { useLocalStorage } from "src/composables/useLocalStorage";

const { darkMode } = useLocalStorage();

export function useDarkMode() {
  const initDarkMode = () => {
    const savedMode = darkMode.get();
    if (savedMode === true) {
      Dark.set(true);
    } else if (savedMode === false) {
      Dark.set(false);
    } else {
      Dark.set("auto");
    }
  };

  const isDarkModeActive = () => Dark.isActive;

  const toggleDarkMode = () => {
    Dark.toggle();
    darkMode.set(Dark.isActive);
  };

  return { initDarkMode, isDarkModeActive, toggleDarkMode };
}
