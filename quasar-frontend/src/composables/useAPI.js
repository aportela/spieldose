import { useAxios } from "src/composables/useAxios";

const { axios } = useAxios();

export function useAPI() {
  const api = {
    common: {
      initialState: () => axios.get("api2/initial_state"),
    }
  };

  return { api };
}
