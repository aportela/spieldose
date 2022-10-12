import { default as playerComponent } from './player.js';

const app = new Vue({
    data: function () {
        return ({
            loading: false,
            tracks: [],
            currentTrackIndex: -1,
            searchQuery: null,
            axios: null,
            currentJWT: null
        });
    },
    computed: {
        currentTrack: function () {
            if (this.tracks && this.tracks.length > 0 && this.currentTrackIndex >= 0) {
                return (this.tracks[this.currentTrackIndex]);
            } else {
                return ({});
            }
        },
        isPlaying: function () {
            return (true);
            //return (this.audio && this.audio.currentAudio && this.audio.currentAudio.currentTime > 0 && !this.audio.currentAudio.paused && !this.audio.currentAudio.ended && this.audio.currentAudio.readyState > 2);
        }
    },
    watch: {
        currentTrackIndex: function (newValue, oldValue) {
            /*
            if (!this.audio) {
                this.audio = document.getElementById('audio');
                this.audio.volume = this.volume / 100;
            } else {
                if (this.audio.currentAudio && this.audio.currentAudio.currentTime > 0 && !this.audio.currentAudio.paused && !this.audio.currentAudio.ended && this.audio.currentAudio.readyState > 2) {
                    this.audio.stop();
                }
            }
            this.audio.src = "/api2/file/" + this.tracks[this.currentTrackIndex].id;
            this.audio.load();
            if (oldValue != -1) {
                this.onPlay();
            }
            */
        }
    },
    created: function () {

        this.axios = axios.create({});
        this.currentJWT = window.spieldose.storage.get('jwt') || null;
        if (this.currentJWT) {
            this.axios.interceptors.request.use((config) => {
                config.headers["SPIELDOSE-JWT"] = this.currentJWT;
                return (config);
            }, (error) => {
                return Promise.reject(error);
            });
        }
        this.axios.interceptors.response.use((response) => {
            console.log(response.headers);
            // warning: axios lowercase received header names
            const apiResponseJWT = response.headers["spieldose-jwt"] || null;
            if (apiResponseJWT) {
                if (apiResponseJWT && apiResponseJWT != this.currentJWT) {
                    window.spieldose.storage.set("jwt", apiResponseJWT);
                    this.currentJWT = apiResponseJWT;
                }
            }
            return response;
        }, (error) => {
            return Promise.reject(error.message);
        });
        this.loadTracks();
    },
    methods: {
        loadTracks: function (query, artist, albumArtist, album) {
            if (artist || album) {
                this.searchQuery = null;
            }
            this.loading = true;
            const url = '/api2/track/search?q=' + (query ? encodeURIComponent(query) : '') + '&artist=' + (artist ? encodeURIComponent(artist) : '') + '&albumArtist=' + (albumArtist ? encodeURIComponent(albumArtist) : '') + '&album=' + (album ? encodeURIComponent(album) : '');
            this.axios.get(url).then(
                (response) => {
                    // handle success
                    this.tracks = response.data.tracks;
                    this.currentTrackIndex = 0;
                }).catch((error) => {
                    // handle error
                    console.log(error);
                }).then(() => {
                    // always executed
                    this.loading = false;
                });
        },
        onSearch: function () {
            this.loadTracks(this.searchQuery);
        },
        onPreviousTrack: function () {
            if (this.currentTrackIndex > 0) {
                this.currentTrackIndex--;
            }
        },
        onNextTrack: function () {
            if (this.tracks && this.tracks.length > 0 && this.currentTrackIndex < this.tracks.length) {
                this.currentTrackIndex++;
            }
        }
    },
    components: {
        'spieldose-player': playerComponent
    }
}).$mount('#app');
