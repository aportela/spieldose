import { EventBus } from 'quasar';

const bus = new EventBus();

export function useBus() {
  return { bus };
}
