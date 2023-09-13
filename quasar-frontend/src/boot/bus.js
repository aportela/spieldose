import { boot } from 'quasar/wrappers';
import { EventBus } from 'quasar';

const bus = new EventBus();

export default boot(({ app }) => {
  // something to do

  // for Options API
  app.config.globalProperties.$bus = bus;

  // for Composition API
  app.provide('bus', bus);
})

export { bus };
