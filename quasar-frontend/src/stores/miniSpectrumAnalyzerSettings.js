import { defineStore } from "pinia";

import { useLocalStorage } from "src/composables/useLocalStorage";

const {
  showMiniSpectrumAnalyzer,
  playerMiniAnalyzerMode,
  playerMiniAnalyzerFPS,
  playerMiniAnalyzerBarSpace,
  playerMiniAnalyzerHeight,
  playerMiniAnalyzerChannelLayout,
  playerMiniAnalyzerShowPeaks,
  playerMiniAnalyzerLedBars,
  playerMiniAnalyzerTrueLeds,
  playerMiniAnalyzerGradient,
  playerMiniAnalyzerLoRes,
} = useLocalStorage();

export const useSidebarMiniSpectrumAnalyzerSettingsStore = defineStore(
  "sidebarMiniSpectrumAnalyzerSettings",
  {
    state: () => ({
      visible: showMiniSpectrumAnalyzer.get() ?? true,
      mode: playerMiniAnalyzerMode.get() ?? 7,
      fps: playerMiniAnalyzerFPS.get() ?? 30,
      barSpace: playerMiniAnalyzerBarSpace.get() ?? 0.2,
      height: playerMiniAnalyzerHeight.get() ?? 40,
      channelLayout: playerMiniAnalyzerChannelLayout.get() ?? "single",
      peaks: playerMiniAnalyzerShowPeaks.get() ?? true,
      ledBars: playerMiniAnalyzerLedBars.get() ?? true,
      trueLeds: playerMiniAnalyzerTrueLeds.get() ?? false,
      gradient: playerMiniAnalyzerGradient.get() ?? "spieldose",
      loRes: playerMiniAnalyzerLoRes.get() ?? false,
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
      isLoResActive: (state) => state.loRes,
    },
    actions: {
      setVisibility(visible) {
        this.visible = !!visible;
        showMiniSpectrumAnalyzer.set(this.visible);
      },
      setMode(mode) {
        if (mode == 10 || (mode >= 0 && mode < 9)) {
          this.mode = mode;
          playerMiniAnalyzerMode.set(this.mode);
        } else {
          console.error(
            "Mini Spectrum Analyzer settings = setMode() => invalid mode",
            mode,
          );
        }
      },
      setFPS(fps) {
        if (fps >= 0 && fps <= 144) {
          this.fps = fps;
          playerMiniAnalyzerFPS.set(this.fps);
        } else {
          console.error(
            "Mini Spectrum Analyzer settings = setFPS() => invalid fps",
            fps,
          );
        }
      },
      setBarSpace(space) {
        if (space >= 0 && space <= 1) {
          this.barSpace = space;
          playerMiniAnalyzerBarSpace.set(this.barSpace);
        } else {
          console.error(
            "Mini Spectrum Analyzer settings = setBarSpace() => invalid space",
            space,
          );
        }
      },
      setHeight(height) {
        if (height > 0) {
          this.height = height;
          playerMiniAnalyzerHeight.set(this.height);
        } else {
          console.error(
            "Mini Spectrum Analyzer settings = setHeight() => invalid height",
            height,
          );
        }
      },
      setChannelLayout(channelLayout) {
        if (
          channelLayout === "single" ||
          channelLayout === "dual-combined" ||
          channelLayout === "dual-horizontal" ||
          channelLayout === "dual-vertical"
        ) {
          this.channelLayout = channelLayout;
          playerMiniAnalyzerChannelLayout.set(this.channelLayout);
        } else {
          console.error(
            "Mini Spectrum Analyzer settings = setChannelLayout() => invalid channel layout",
            channelLayout,
          );
        }
      },
      setPeaksVisibility(visible) {
        this.peaks = !!visible;
        playerMiniAnalyzerShowPeaks.set(this.peaks);
      },
      setLedBars(active) {
        this.ledBars = !!active;
        playerMiniAnalyzerLedBars.set(this.ledBars);
      },
      setTrueLeds(active) {
        this.trueLeds = !!active;
        playerMiniAnalyzerTrueLeds.set(this.trueLeds);
      },
      setGradient(gradient) {
        if (gradient) {
          this.gradient = gradient;
          playerMiniAnalyzerGradient.set(this.gradient);
        } else {
          console.error(
            "Mini Spectrum Analyzer settings = setGradient() => invalid gradient",
            gradient,
          );
        }
      },
      setLoRes(active) {
        this.loRes = !!active;
        playerMiniAnalyzerLoRes.set(this.loRes);
      },
    },
  },
);
