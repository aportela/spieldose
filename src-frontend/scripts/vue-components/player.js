//import { AudioMotionAnalyzer } from AudioMotionAnalizer;

const template = function () {
    return `
        <div class="player__container">
            <div class="player__body">
                <audio id="audio" class="is-hidden"></audio>
                <div class="body__cover">
                    <ul class="list list--cover">
                        <li>
                            <slot name="top-left-icon"></slot>
                        </li>
                        <li>
                            <a class="list__link" href=""></a>
                        </li>
                        <li>
                            <slot name="top-right-icon"></slot>
                        </li>
                    </ul>

                    <img v-if="coverURL" :src="coverURL" alt="Album cover" @error="coverURL = null"/>
                    <div v-else style="background: #efefef; width: 300px; height: 300px;">
                    <img src="images/vinyl.png" alt="Vinyl" />
                    </div>
                    <div class="range"></div>
                </div>

                <div id="container" @click="onChangeAudioMotionAnalyzerMode"></div>

                <div class="body__info">
                    <div class="info__album">{{ track.title }}</div>

                    <div class="info__song">{{ track.album }}</div>

                    <div class="info__artist">{{ track.artist }}</div>
                </div>

                <div class="field">
                    <div class="control has-icons-left has-icons-right">
                        <span class="icon is-left" style="height: 1em;">
                            <i class="fa-solid fa-volume-high" style="color: #d30320;" :style="'opacity: ' + ((volume / 100)+0.4)"></i>
                        </span>
                        <input style="padding-left: 3.5em; padding-right: 3.5em;"
                            class="slider is-fullwidth is-small is-circle" step="1" min="0" max="100" type="range"
                            v-model.number="volume">
                            <span class="icon is-right" style="height: 1em;">
                            <small>{{ volume }}%</small>
                        </span>
                    </div>
                </div>

                <div class="field">
                    <div class="control has-icons-left has-icons-right">
                        <span class="icon is-left" style="height: 1em;">
                            <small>{{ currentTime }}</small>

                        </span>
                        <input style="padding-left: 3.5em; padding-right: 3.5em;"
                            class="slider is-fullwidth is-small is-circle" min="0" max="1" step="0.01" type="range"
                            v-model.number="position">
                        <span class="icon is-right" style="height: 1em;">
                            <small>{{ duration }}</small>
                        </span>
                    </div>
                </div>

                <div class="body__buttons">
                    <ul class="list list--buttons">
                        <li><a href="#" class="list__link" @click.prevent="onPreviousTrack"><i
                                    class="fa fa-step-backward"></i></a></li>

                        <li><a href="#" class="list__link" @click.prevent="onPlay"><i class="fa fa-play"></i></a>
                        </li>

                        <li><a href="#" class="list__link" @click.prevent="onNextTrack"><i
                                    class="fa fa-step-forward"></i></a></li>
                    </ul>
                </div>
            </div>

            <div class="player__footer">
                <ul class="list list--footer">
                    <li><a href="#" class="list__link" title="Toggle navigation menu"><i class="fa fa-navicon"></i></a>
                    </li>

                    <li><a href="#" class="list__link" title="Love/unlove track"><i class="fa fa-heart"></i></a>
                    </li>

                    <li><a href="#" class="list__link" title="Toggle random sort"><i class="fa fa-random"></i></a>
                    </li>

                    <li><a href="#" class="list__link" title="Toggle repeat mode"><i class="fa fa-undo"></i></a>
                    </li>

                    <li><a href="#" class="list__link" title="Download track"><i class="fa-solid fa-download"></i></a>
                    </li>

                    <li><a href="#" class="list__link" title="Toggle details"><i
                                class="fa-regular fa-rectangle-list"></i></a></li>

                </ul>
            </div>
        </div>
    `;
}

export default {
    name: 'spieldose-player',
    template: template(),
    data: function () {
        return ({
            audio: null,
            volume: 8,
            position: 0,
            audioMotion: null,
            currentTime: "00:00",
            duration: "00:00",
            audioMotionMode: 3,
            coverURL: null
        });
    },
    props: [
        'track'
    ],
    computed: {
        trackId: function () {
            return (this.track ? this.track.id : null);
        },
        isPlaying: function () {
            return (this.audio && this.audio.currentAudio && this.audio.currentAudio.currentTime > 0 && !this.audio.currentAudio.paused && !this.audio.currentAudio.ended && this.audio.currentAudio.readyState > 2);
        }
    },
    watch: {
        trackId: function (newValue, oldValue) {
            if (newValue) {
                if (this.audio.currentAudio && this.audio.currentAudio.currentTime > 0 && !this.audio.currentAudio.paused && !this.audio.currentAudio.ended && this.audio.currentAudio.readyState > 2) {
                    this.audio.stop();
                }
                this.coverURL = null;
                this.audio.src = "/api2/file/" + this.track.id;
                //const url = this.track.thumbnailURL;
                const url = "/api2/track/thumbnail/400/400/" + this.track.id;
                let img = new Image();
                img.src = url
                img.onload = () => {
                    this.coverURL = url;
                }
                img.onerror = () => {
                    this.coverURL = null;
                }
                this.audio.load();
                if (newValue && oldValue) {
                    this.onPlay();
                }
            }
        },
        volume: function (newValue) {
            if (this.audio) {
                this.audio.volume = newValue / 100;
            }
        }
    },
    created: function () {
    },
    mounted: function () {
        this.audio = document.getElementById('audio');
        this.audio.volume = this.volume / 100;
        /*
        this.audioMotion = new AudioMotionAnalyzer(
            document.getElementById('container'),
            {
                source: document.getElementById('audio'),
                mode: 3,
                height: 40,
                ledBars: false,
                showScaleX: false,
                showScaleY: false,
                stereo: false,
                splitGradient: false
            }
        );
        */
    },
    methods: {
        onPreviousTrack: function () {
            this.$emit("previous", true)
        },
        onNextTrack: function () {
            this.$emit("next", true)
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
        onChangeAudioMotionAnalyzerMode: function () {
            this.audioMotionMode++;
            if (this.audioMotionMode > 8) {
                this.audioMotionMode = 0;
            }
            this.audioMotion.setOptions({ mode: this.audioMotionMode });
        }
    }
};
