import { defineStore } from "pinia";

import { useLocalStorage } from "src/composables/useLocalStorage";

const {
  showMiniSpectrumAnalyzer,
  playerMiniAnalyzerMode,
  playerMiniAnalyzerFPS,
  playerMiniAnalyzerBarSpace,
  playerMiniAnalyzerHeight,
  playerMiniAnalyzerChannelLayout,
} = useLocalStorage();

export const useMiniSpectrumAnalyzerSettingsStore = defineStore(
  "miniSpectrumAnalyzerSettings",
  {
    state: () => ({
      visible: showMiniSpectrumAnalyzer.get() ?? true,
      mode: playerMiniAnalyzerMode.get() ?? 7,
      fps: playerMiniAnalyzerFPS.get() ?? 30,
      barSpace: playerMiniAnalyzerBarSpace.get() ?? 0.2,
      height: playerMiniAnalyzerHeight.get() ?? 40,
      channelLayout: playerMiniAnalyzerChannelLayout.get() ?? "single",
    }),

    getters: {
      isVisible: (state) => state.visible,
      currentMode: (state) => state.mode,
      currentFPS: (state) => state.fps,
      currentBarSpace: (state) => state.barSpace,
      currentHeight: (state) => state.height,
      currentChannelLayout: (state) => state.channelLayout,
    },
    actions: {
      setVisibility(visible) {
        this.visible = !!visible;
        showMiniSpectrumAnalyzer.set(this.visible);
      },
      setMode(mode) {
        if (mode > 0 && mode < 9) {
          this.mode = mode;
          playerMiniAnalyzerMode.set(mode);
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
          playerMiniAnalyzerFPS.set(fps);
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
          playerMiniAnalyzerBarSpace.set(space);
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
          playerMiniAnalyzerHeight.set(height);
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
          playerMiniAnalyzerChannelLayout.set(channelLayout);
        } else {
          console.error(
            "Mini Spectrum Analyzer settings = setChannelLayout() => invalid channel layout",
            channelLayout,
          );
        }
      },
    },
  },
);
