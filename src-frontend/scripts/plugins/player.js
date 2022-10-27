import { reactive } from "vue";

export default {
    install: (app, options) => {
        let playerInstance = reactive({
            hasPreviousUserInteractions: false
        });
        app.config.globalProperties.$player = playerInstance;
    }
}
