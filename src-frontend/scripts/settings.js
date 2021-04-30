const getSpieldoseSettings = function() {
    "use strict";

    const defaultSettings = {
        currentSession: {
            volume: 1
        }
    };


    let module = {
        settings: null
    };

    module.load = function() {
        let data = localStorage.getItem("spieldose-settings");
        if (data) {
            module.settings = JSON.parse(data);
        } else {
            module.settings = defaultSettings;
        }

    };
    module.save = function() {
        localStorage.setItem("spieldose-settings", JSON.stringify(module.settings || defaultSettings));
    };

    module.getCurrentSessionVolume = function() {
        try {
            if (! module.settings) {
                module.load();
            }
            return(module.settings.currentSession.volume);
        } catch (e) {}
    }

    module.setCurrentSessionVolume = function(volume) {
        try {
            if (! module.settings) {
                module.load();
            }
            module.settings.currentSession.volume = volume;
            module.save();
        } catch (e) {}
    }

    return (module);
};

let settings = getSpieldoseSettings();
export default settings;
