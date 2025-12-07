import { LocalStorage } from "quasar";
import { LOCAL_STORAGE_NAMESPACE } from "src/constants";
import { type VinylAnimation, type SpectrumAnalyzerChannelLayout } from "src/types/common";
import { type AudioMotionAnalyzerOptionColorMode } from "src/types/common";

export type StorageValue = string | number | boolean | null | object;

const createStorageEntry = <T extends StorageValue>(
  key: string,
  defaultValue: T
) => ({
  get(): T {
    try {
      const stored = LocalStorage.getItem(LOCAL_STORAGE_NAMESPACE + key);
      return stored === null ? defaultValue : (stored as T);
    } catch (error) {
      console.error("Error accessing localStorage:", error);
      return defaultValue;
    }
  },
  set(value: T) {
    try {
      LocalStorage.set(
        LOCAL_STORAGE_NAMESPACE + key,
        value as unknown as StorageValue
      );
    } catch (error) {
      console.error("Error accessing localStorage:", error);
    }
  },
  remove() {
    try {
      LocalStorage.remove(LOCAL_STORAGE_NAMESPACE + key);
    } catch (error) {
      console.error("Error accessing localStorage:", error);
    }
  },
});

const darkMode = createStorageEntry<boolean>("darkMode", false);
const playerVolume = createStorageEntry<number>("playerVolume", 1);
const playerMuted = createStorageEntry<boolean>("playerMuted", false);
const playerVinylAnimation = createStorageEntry<VinylAnimation>("playerVinylAnimation", null);

export {
  createStorageEntry,
  darkMode,
  playerVolume,
  playerMuted,
  playerVinylAnimation,
};
