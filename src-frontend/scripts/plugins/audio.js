let audioInterface = {
    element: null
};

audioInterface.getElement = () => {
    if (!audioInterface.element) {
        audioInterface.element = document.getElementById('audio');
    }
    return (audioInterface.element);
}
audioInterface.setVolume = (volume) => {
    if (audioInterface) {
        audioInterface.getElement().volume = volume;
    }
}

export default {
    install: (app, options) => {
        app.config.globalProperties.$audio = audioInterface;
    }
}
