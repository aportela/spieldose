import { defineStore, acceptHMRUpdate } from 'pinia';
import { createStorageEntry } from 'src/composables/localStorage';
import { type SpectrumAnalyzerChannelLayout, type AudioMotionAnalyzerOptionColorMode } from 'src/types/common';

const localStorageShowMiniSpectrumAnalyzer = createStorageEntry<boolean>("visualizations.sidebar.spectrumAnalyzer.visible", true);
const localStoragePlayerMiniAnalyzerMode = createStorageEntry<number>("visualizations.sidebar.spectrumAnalyzer.mode", 7);
const localStoragePlayerMiniAnalyzerFPS = createStorageEntry<number>("visualizations.sidebar.spectrumAnalyzer.fps", 30);
const localStoragePlayerMiniAnalyzerBarSpace = createStorageEntry<number>("visualizations.sidebar.spectrumAnalyzer.barSpace", 0.2);
const localStoragePlayerMiniAnalyzerHeight = createStorageEntry<number>("visualizations.sidebar.spectrumAnalyzer.height", 40);
const localStoragePlayerMiniAnalyzerChannelLayout = createStorageEntry<SpectrumAnalyzerChannelLayout>("visualizations.sidebar.spectrumAnalyzer.channelLayout", "single");
const localStoragePlayerMiniAnalyzerShowPeaks = createStorageEntry<boolean>("visualizations.sidebar.spectrumAnalyzer.showPeaks", true);
const localStoragePlayerMiniAnalyzerLedBars = createStorageEntry<boolean>("visualizations.sidebar.spectrumAnalyzer.ledBars", true);
const localStoragePlayerMiniAnalyzerTrueLeds = createStorageEntry<boolean>("visualizations.sidebar.spectrumAnalyzer.trueLeds", true);
const localStoragePlayerMiniAnalyzerColorMode = createStorageEntry<AudioMotionAnalyzerOptionColorMode>("visualizations.sidebar.spectrumAnalyzer.colorMode", "gradient");
const localStoragePlayerMiniAnalyzerGradient = createStorageEntry<string>("visualizations.sidebar.spectrumAnalyzer.gradient", "spieldose");
const localStoragePlayerMiniAnalyzerLoRes = createStorageEntry<boolean>("visualizations.sidebar.spectrumAnalyzer.loRes", false);

interface State {
  settings: {
    visible: boolean;
    mode: number;
    fps: number;
    barSpace: number;
    height: number;
    channelLayout: SpectrumAnalyzerChannelLayout;
    showPeaks: boolean;
    ledBars: boolean;
    trueLeds: boolean;
    colorMode: AudioMotionAnalyzerOptionColorMode;
    gradient: string;
    loRes: boolean;
  }
};

export const useSidebarSpectrumAnalyzerSettingsStore = defineStore('sidebarMiniSpectrumAnalyzerSettingsStore', {
  state: (): State => ({
    settings: {
      visible: localStorageShowMiniSpectrumAnalyzer.get(),
      mode: localStoragePlayerMiniAnalyzerMode.get(),
      fps: localStoragePlayerMiniAnalyzerFPS.get(),
      barSpace: localStoragePlayerMiniAnalyzerBarSpace.get(),
      height: localStoragePlayerMiniAnalyzerHeight.get(),
      channelLayout: localStoragePlayerMiniAnalyzerChannelLayout.get(),
      showPeaks: localStoragePlayerMiniAnalyzerShowPeaks.get(),
      ledBars: localStoragePlayerMiniAnalyzerLedBars.get(),
      trueLeds: localStoragePlayerMiniAnalyzerTrueLeds.get(),
      colorMode: localStoragePlayerMiniAnalyzerColorMode.get(),
      gradient: localStoragePlayerMiniAnalyzerGradient.get(),
      loRes: localStoragePlayerMiniAnalyzerLoRes.get(),
    },
  }),
  getters: {
    visible: (state) => state.settings.visible,
    mode: (state) => state.settings.mode,
    fps: (state) => state.settings.fps,
    barSpace: (state) => state.settings.barSpace,
    height: (state) => state.settings.height,
    channelLayout: (state) => state.settings.channelLayout,
    showPeaks: (state) => state.settings.showPeaks,
    ledBars: (state) => state.settings.ledBars,
    trueLeds: (state) => state.settings.trueLeds,
    colorMode: (state) => state.settings.colorMode,
    gradient: (state) => state.settings.gradient,
    loRes: (state) => state.settings.loRes
  },
  actions: {
    setVisible(visible: boolean) {
      this.settings.visible = visible;
      localStorageShowMiniSpectrumAnalyzer.set(this.settings.visible);
    },
    setMode(mode: number) {
      if (mode == 10 || (mode >= 0 && mode < 9)) {
        this.settings.mode = mode;
        localStoragePlayerMiniAnalyzerMode.set(this.settings.mode);
      } else {
        console.error(
          "Mini Spectrum Analyzer settings = setMode() => invalid mode",
          mode,
        );
      }
    },
    setFps(fps: number) {
      if (fps >= 0 && fps <= 144) {
        this.settings.fps = fps;
        localStoragePlayerMiniAnalyzerFPS.set(this.settings.fps);
      } else {
        console.error(
          "Mini Spectrum Analyzer settings = setFPS() => invalid fps",
          fps,
        );
      }
    },
    setBarSpace(space: number) {
      if (space >= 0 && space <= 1) {
        this.settings.barSpace = space;
        localStoragePlayerMiniAnalyzerBarSpace.set(this.settings.barSpace);
      } else {
        console.error(
          "Mini Spectrum Analyzer settings = setBarSpace() => invalid space",
          space,
        );
      }
    },
    setHeight(height: number) {
      if (height > 0) {
        this.settings.height = height;
        localStoragePlayerMiniAnalyzerHeight.set(this.settings.height);
      } else {
        console.error(
          "Mini Spectrum Analyzer settings = setHeight() => invalid height",
          height,
        );
      }
    },
    setChannelLayout(channelLayout: SpectrumAnalyzerChannelLayout) {
      this.settings.channelLayout = channelLayout;
      localStoragePlayerMiniAnalyzerChannelLayout.set(this.settings.channelLayout);
    },
    setShowPeaks(visible: boolean) {
      this.settings.showPeaks = visible;
      localStoragePlayerMiniAnalyzerShowPeaks.set(this.settings.showPeaks);
    },
    setLedBars(active: boolean) {
      this.settings.ledBars = active;
      localStoragePlayerMiniAnalyzerLedBars.set(this.settings.ledBars);
    },
    setTrueLeds(active: boolean) {
      this.settings.trueLeds = active;
      localStoragePlayerMiniAnalyzerTrueLeds.set(this.settings.trueLeds);
    },
    setColorMode(colorMode: AudioMotionAnalyzerOptionColorMode) {
      this.settings.colorMode = colorMode;
      localStoragePlayerMiniAnalyzerColorMode.set(this.settings.colorMode);
    },
    setGradient(gradient: string) {
      if (gradient) {
        this.settings.gradient = gradient;
        localStoragePlayerMiniAnalyzerGradient.set(this.settings.gradient);
      } else {
        console.error(
          "Mini Spectrum Analyzer settings = setGradient() => invalid gradient",
          gradient,
        );
      }
    },
    setLoRes(active: boolean) {
      this.settings.loRes = active;
      localStoragePlayerMiniAnalyzerLoRes.set(this.settings.loRes);
    },
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useSidebarSpectrumAnalyzerSettingsStore, import.meta.hot));
}
