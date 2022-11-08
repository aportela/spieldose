import { reactive } from "vue";

export default {
    install: (app, options) => {
        const savedPlaylist = app.config.globalProperties.$localStorage.get('currentPlaylist');
        const savedPlaylistIndex = app.config.globalProperties.$localStorage.get('currentPlaylistTrackIndex');
        const previousPlaylistFound = (savedPlaylist && savedPlaylist.length > 0 && savedPlaylistIndex >= 0 && savedPlaylistIndex < savedPlaylist.length);
        if (previousPlaylistFound) {
            console.log("Previous playlist found, restoring...");
        }

        let playerInstance = reactive({
            hasPreviousUserInteractions: false,
            currentPlaylist: {
                tracks: previousPlaylistFound ? savedPlaylist : [],
                trackIndex: previousPlaylistFound ? savedPlaylistIndex : -1
            },
            get currentTrack() {
                return (this.currentPlaylist && this.currentPlaylist.tracks && this.currentPlaylist.tracks.length > 0 ? this.currentPlaylist.tracks[this.currentPlaylist.trackIndex] : {});
            },
            replaceCurrentPlaylist: function (tracks) {
                console.log("Replacing current playlist");
                this.hasPreviousUserInteractions = true;
                this.currentPlaylist.tracks = tracks;
                this.currentPlaylist.trackIndex = 0;
                app.config.globalProperties.$localStorage.set('currentPlaylist', tracks);
                app.config.globalProperties.$localStorage.set('currentPlaylistTrackIndex', 0);
            },
            onPreviousTrack: function () {
                if (this.currentPlaylist.trackIndex > 0) {
                    this.currentPlaylist.trackIndex--;
                    this.hasPreviousUserInteractions = true;
                }
            },
            onNextTrack: function () {
                if (this.currentPlaylist.tracks && this.currentPlaylist.tracks.length > 0 && this.currentPlaylist.trackIndex < (this.currentPlaylist.tracks.length - 1)) {
                    this.currentPlaylist.trackIndex++;
                    this.hasPreviousUserInteractions = true;
                }
            },
            onChangeCurrentTrackIndex: function (index) {
                if (index >= 0 && index < this.currentPlaylist.tracks.length) {
                    this.currentPlaylist.trackIndex = index;
                    this.hasPreviousUserInteractions = true;
                    app.config.globalProperties.$localStorage.set('currentPlaylistTrackIndex', index);
                }
            }
        });

        app.config.globalProperties.$player = playerInstance;
    }
}
