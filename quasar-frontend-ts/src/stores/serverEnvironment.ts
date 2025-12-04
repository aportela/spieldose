import { defineStore, acceptHMRUpdate } from 'pinia';

import { type EnvironmentType } from "src/types/common";

interface State {
  allowSignUp: boolean;
  environment: EnvironmentType;
};

export const useServerEnvironmentStore = defineStore('serverEnvironment', {
  state: (): State => ({
    allowSignUp: true,
    environment: "production",
  }),
  getters: {
    isSignUpAllowed(state): boolean {
      return state.allowSignUp
    },
    isCurrentEnvironmentDevelopment(state): boolean {
      return state.environment == "development"
    },
  },
  actions: {
    set(
      allowSignUp: boolean = false,
      environment: EnvironmentType = "production",
    ): void {
      this.allowSignUp = !!allowSignUp;
      this.environment = environment;
    },
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useServerEnvironmentStore, import.meta.hot));
}
