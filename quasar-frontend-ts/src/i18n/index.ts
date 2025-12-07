import { Lang } from "quasar";
import enUS from "./en-US";
//import esES from "./es-ES";
//import glGL from "./gl-GL";
// @ts-expect-error: missing TypeScript type definitions
import { default as enUS_Quasar } from "../../node_modules/quasar/lang/en-US";
// @ts-expect-error: missing TypeScript type definitions
import { default as esES_Quasar } from "../../node_modules/quasar/lang/es";

const messages = {
  "en-US": enUS,
  //"es-ES": esES,
  //"gl-GL": glGL,
};

const availableSystemLocales: string[] = Object.keys(messages);

const localeSelectorOptionItems = [
  { shortLabel: "EN", label: "English", value: "en-US" },
  { shortLabel: "ES", label: "Español", value: "es-ES" },
  { shortLabel: "GL", label: "Galego", value: "gl-GL" },
];

const availableLocaleSelectorOptionItems = localeSelectorOptionItems.filter((l) => availableSystemLocales.includes(l.value));

const getlocaleSelectorOptionItem = (locale: string) => {
  const currentIndex = availableLocaleSelectorOptionItems.findIndex((l) => l.value === locale);
  return (availableLocaleSelectorOptionItems[currentIndex >= 0 ? currentIndex : 0]!);
};

const quasarLanguages = {
  "en-US": enUS_Quasar,
  "es-ES": esES_Quasar,
  "gl-GL": esES_Quasar,
};

const setQuasarLanguage = (locale: string) => {
  switch (locale) {
    case "en-US":
      Lang.set(quasarLanguages["en-US"]);
      break;
    case "es-ES":
      console.log(1);
      Lang.set(quasarLanguages["es-ES"]);
      console.log(2);
      break;
    case "gl-GL":
      Lang.set(quasarLanguages["gl-GL"]);
      break;
    default:
      console.error("Invalid locale for quasar language:", locale);
      Lang.set(quasarLanguages["en-US"]);
      break;
  }
};

export { messages, availableSystemLocales, availableLocaleSelectorOptionItems, getlocaleSelectorOptionItem, setQuasarLanguage };
