type EnvironmentType = "development" | "production";

type ValidAuthTypes = "Bearer";

type VinylAnimation = "rotate" | null;

type SpectrumAnalyzerChannelLayout = "single" | "dual-combined" | "dual-horizontal" | "dual-vertical";

type PlayerStatus = "stopped" | "playing" | "paused";

type SortOrder = "ASC" | "DESC";

interface SelectorOption {
  label: string;
  value: SortOrder;
}

const sortOrderSelectorOptions: SelectorOption[] = [
  {
    label: "Ascending",
    value: "ASC",
  },
  {
    label: "Descending",
    value: "DESC",
  }
];

export {
  type EnvironmentType, type ValidAuthTypes, type VinylAnimation, type SpectrumAnalyzerChannelLayout, type PlayerStatus, type SortOrder, type SelectorOption, sortOrderSelectorOptions
};
