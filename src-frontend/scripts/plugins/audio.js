export default {
    install: (app, options) => {
        app.config.globalProperties.$audio = document.getElementById('audio')
    }
}
