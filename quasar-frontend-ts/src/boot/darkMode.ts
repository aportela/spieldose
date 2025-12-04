import { defineBoot } from '#q-app/wrappers'
import { useDarkModeStore } from "src/stores/darkMode";

export default defineBoot((/* { app, router, ... } */) => {
  useDarkModeStore();
});
