import AudioMotionAnalyzer from 'https://cdn.skypack.dev/audiomotion-analyzer?min';

const app = new Vue({
    data: function () {
        return ({
            tracks: [],
            currentTrackIndex: -1,
            audio: null,
            volume: 16,
            position: 0,
            audioMotion: null,
            currentTime: "00:00",
            duration: "00:00",
            audioMotionMode: 3
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
            return (this.audio && this.audio.currentAudio && this.audio.currentAudio.currentTime > 0 && !this.audio.currentAudio.paused && !this.audio.currentAudio.ended && this.audio.currentAudio.readyState > 2);
        }
    },
    watch: {
        currentTrackIndex: function (newValue, oldValue) {
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
        },
        volume: function (newValue) {
            if (this.audio) {
                this.audio.volume = newValue / 100;
            }
        }
    },
    created: function () {
        this.loadTracks();
    },
    mounted: function() {
        this.audioMotion = new AudioMotionAnalyzer(
            document.getElementById('container'),
            {
                source: document.getElementById('audio'),
                mode: 3,
                /*
                connectSpeakers: false,
                fftSize : 1024,
 
                showPeaks :true,
                stereo : true
                */
                height: 40,
                // you can set other options below - check the docs!
                //mode: 3,
                /*
                barSpace: .6,
                */
                ledBars: false,
                showScaleX: false,
                showScaleY: false,
                stereo: false,
                splitGradient: false
            }
        );
    },
    methods: {
        loadTracks: function () {
            Vue.http.get("/api2/track/search").then(
                response => {
                    this.tracks = response.body.tracks;
                    this.currentTrackIndex = 0;
                },
                response => {
                    console.log(response);
                }
            );
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
        },
        onPlay: function () {
            if (this.isPlaying) {
                this.audio.stop();
            }
            let playPromise = this.audio.play();
            this.audio.addEventListener('timeupdate', (track) => {
                const currentProgress = this.audio.currentTime / this.audio.duration;
                this.duration = this.formatSecondsAsTime(Math.floor(this.audio.duration).toString());
                this.currentTime = this.formatSecondsAsTime(Math.floor(this.audio.currentTime).toString());
                if (!isNaN(currentProgress)) {
                    this.position = currentProgress.toFixed(2);
                } else {
                    this.position = 0;
                }
                //this.currentPlayedSeconds = Math.floor(aa.currentTime).toString();
            });
            if (playPromise !== undefined) {
                //console.log(playPromise);
                playPromise.then(() => {
                }).catch((error) => {
                    //this.$audioplayer.playback.pause();
                    this.audio.currentTime = 0;
                });
            }
        },
        formatSecondsAsTime: function (secs, format) {
            var hr = Math.floor(secs / 3600);
            var min = Math.floor((secs - (hr * 3600)) / 60);
            var sec = Math.floor(secs - (hr * 3600) - (min * 60));

            if (min < 10) {
                min = "0" + min;
            }
            if (sec < 10) {
                sec = "0" + sec;
            }

            return min + ':' + sec;
        },
        onChangeAudioMotionAnalyzerMode: function() {
            this.audioMotionMode++;
            if (this.audioMotionMode > 8) {
                this.audioMotionMode = 0;
            }
            this.audioMotion.setOptions({ mode: this.audioMotionMode });
            console.log(this.audioMotionMode);
        }
    }
}).$mount('#app');
