import { defineStore, acceptHMRUpdate } from 'pinia';
import {
  showMiniSpectrumAnalyzer as localStorageShowMiniSpectrumAnalyzer,
  playerMiniAnalyzerMode as localStoragePlayerMiniAnalyzerMode,
  playerMiniAnalyzerFPS as localStoragePlayerMiniAnalyzerFPS,
  playerMiniAnalyzerBarSpace as localStoragePlayerMiniAnalyzerBarSpace,
  playerMiniAnalyzerHeight as localStoragePlayerMiniAnalyzerHeight,
  playerMiniAnalyzerChannelLayout as localStoragePlayerMiniAnalyzerChannelLayout,
  playerMiniAnalyzerShowPeaks as localStoragePlayerMiniAnalyzerShowPeaks,
  playerMiniAnalyzerLedBars as localStoragePlayerMiniAnalyzerLedBars,
  playerMiniAnalyzerTrueLeds as localStoragePlayerMiniAnalyzerTrueLeds,
  playerMiniAnalyzerGradient as localStoragePlayerMiniAnalyzerGradient,
  playerMiniAnalyzerLoRes as localStoragePlayerMiniAnalyzerLoRes,
} from 'src/composables/localStorage';
import { type SpectrumAnalyzerChannelLayout } from 'src/types/common';

interface State {
  visible: boolean;
  mode: number;
  fps: number;
  barSpace: number;
  height: number;
  channelLayout: SpectrumAnalyzerChannelLayout;
  peaks: boolean;
  ledBars: boolean;
  trueLeds: boolean;
  gradient: string;
  loRes: boolean;
};

export const useMiniSpectrumAnalyzerSettingsStore = defineStore('miniSpectrumAnalyzerSettingsStore', {
  state: (): State => ({
    visible: localStorageShowMiniSpectrumAnalyzer.get(),
    mode: localStoragePlayerMiniAnalyzerMode.get(),
    fps: localStoragePlayerMiniAnalyzerFPS.get(),
    barSpace: localStoragePlayerMiniAnalyzerBarSpace.get(),
    height: localStoragePlayerMiniAnalyzerHeight.get(),
    channelLayout: localStoragePlayerMiniAnalyzerChannelLayout.get(),
    peaks: localStoragePlayerMiniAnalyzerShowPeaks.get(),
    ledBars: localStoragePlayerMiniAnalyzerLedBars.get(),
    trueLeds: localStoragePlayerMiniAnalyzerTrueLeds.get(),
    gradient: localStoragePlayerMiniAnalyzerGradient.get(),
    loRes: localStoragePlayerMiniAnalyzerLoRes.get(),
  }),
  getters: {
    isVisible: (state) => state.visible,
    currentMode: (state) => state.mode,
    currentFPS: (state) => state.fps,
    currentBarSpace: (state) => state.barSpace,
    currentHeight: (state) => state.height,
    currentChannelLayout: (state) => state.channelLayout,
    showPeaks: (state) => state.peaks,
    ledBarsActive: (state) => state.ledBars,
    trueLedsActive: (state) => state.trueLeds,
    currentGradient: (state) => state.gradient,
    isLoResActive: (state) => state.loRes
  },
  actions: {
    setVisibility(visible: boolean) {
      this.visible = visible;
      localStorageShowMiniSpectrumAnalyzer.set(this.visible);
    },
    setMode(mode: number) {
      if (mode == 10 || (mode >= 0 && mode < 9)) {
        this.mode = mode;
        localStoragePlayerMiniAnalyzerMode.set(this.mode);
      } else {
        console.error(
          "Mini Spectrum Analyzer settings = setMode() => invalid mode",
          mode,
        );
      }
    },
    setFPS(fps: number) {
      if (fps >= 0 && fps <= 144) {
        this.fps = fps;
        localStoragePlayerMiniAnalyzerFPS.set(this.fps);
      } else {
        console.error(
          "Mini Spectrum Analyzer settings = setFPS() => invalid fps",
          fps,
        );
      }
    },
    setBarSpace(space: number) {
      if (space >= 0 && space <= 1) {
        this.barSpace = space;
        localStoragePlayerMiniAnalyzerBarSpace.set(this.barSpace);
      } else {
        console.error(
          "Mini Spectrum Analyzer settings = setBarSpace() => invalid space",
          space,
        );
      }
    },
    setHeight(height: number) {
      if (height > 0) {
        this.height = height;
        localStoragePlayerMiniAnalyzerHeight.set(this.height);
      } else {
        console.error(
          "Mini Spectrum Analyzer settings = setHeight() => invalid height",
          height,
        );
      }
    },
    setChannelLayout(channelLayout: SpectrumAnalyzerChannelLayout) {
      this.channelLayout = channelLayout;
      localStoragePlayerMiniAnalyzerChannelLayout.set(this.channelLayout);
    },
    setPeaksVisibility(visible: boolean) {
      this.peaks = visible;
      localStoragePlayerMiniAnalyzerShowPeaks.set(this.peaks);
    },
    setLedBars(active: boolean) {
      this.ledBars = active;
      localStoragePlayerMiniAnalyzerLedBars.set(this.ledBars);
    },
    setTrueLeds(active: boolean) {
      this.trueLeds = active;
      localStoragePlayerMiniAnalyzerTrueLeds.set(this.trueLeds);
    },
    setGradient(gradient: string) {
      if (gradient) {
        this.gradient = gradient;
        localStoragePlayerMiniAnalyzerGradient.set(this.gradient);
      } else {
        console.error(
          "Mini Spectrum Analyzer settings = setGradient() => invalid gradient",
          gradient,
        );
      }
    },
    setLoRes(active: boolean) {
      this.loRes = active;
      localStoragePlayerMiniAnalyzerLoRes.set(this.loRes);
    },
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useMiniSpectrumAnalyzerSettingsStore, import.meta.hot));
}
