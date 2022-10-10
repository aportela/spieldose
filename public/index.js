import { default as playerComponent } from './player.js';

const app = new Vue({
    data: function () {
        return ({
            loading: false,
            tracks: [],
            currentTrackIndex: -1,
            searchQuery: null
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
            return(true);
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
        this.loadTracks();
    },
    methods: {
        loadTracks: function (query, artist, album) {
            if (artist ||album) {
                this.searchQuery = null;
            }
            this.loading = true;
            const url = '/api2/track/search?q=' + (query ? encodeURIComponent(query): '')+ '&artist=' + (artist ? encodeURIComponent(artist): '') + '&album=' + (album ? encodeURIComponent(album): '');
            Vue.http.get(url).then(                
                response => {
                    this.loading = false;
                    this.tracks = response.body.tracks;
                    this.currentTrackIndex = 0;
                },
                response => {
                    this.loading = false;
                    console.log(response);
                }
            );
        },
        onSearch: function() {
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
