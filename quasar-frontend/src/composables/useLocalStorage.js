import { LocalStorage } from "quasar";

export function useLocalStorage() {
  const get = (key, defaultValue = null) => {
    try {
      const savedValue = LocalStorage.getItem(key);
      return savedValue === null ? defaultValue : savedValue;
    } catch (error) {
      console.error("Error accessing localStorage:", error);
      return defaultValue;
    }
  };

  const set = (key, value) => {
    try {
      LocalStorage.setItem(key, value);
    } catch (error) {
      console.error("Error accessing localStorage:", error);
    }
  };

  const darkMode = {
    get() {
      return get("darkMode");
    },
    set(value) {
      set("darkMode", !!value);
    },
  };

  return {
    darkMode
  };
}
