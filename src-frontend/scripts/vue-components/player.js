import AudioMotionAnalyzer from 'audiomotion-analyzer';

const template = function () {
    return `
        <div>
            <audio id="audio" class="is-hidden"></audio>
            <div class="player__body" style="max-width: 400px;">
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
                    <div v-if="customVinyl" @click.prevent="customVinyl =! customVinyl">
                        <div id="rotating_album_cover" :class="{ 'is_rotating_album_cover': playerEvents.isPlaying }">
                            <img v-if="coverURL" :src="coverURL" alt="Album cover" @error="coverURL = null"/>
                        </div>
                    </div>
                    <div v-else @click.prevent="customVinyl =! customVinyl">
                        <img v-if="coverURL" :src="coverURL" alt="Album cover" @error="coverURL = null"/>
                        <img v-else src="images/vinyl.png" alt="Vinyl" />
                    </div>
                    <!--
                    <div class="range"></div>
                    -->
                </div>

                <div id="container" v-if="showAnalyzer" @click="onChangeaudioElementMotionAnalyzerMode" style="opacity: 0.9"></div>

                <div class="body__info">
                    <div class="info__album">{{ track.title }}</div>

                    <div class="info__song">{{ track.album }}</div>

                    <div class="info__artist">{{ track.artist }}</div>
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
                        <li><a href="#" class="list__link" :class="{ 'disabled': this.playerEvents.isLoading }" @click.prevent="onPreviousTrackButtonClick"><i
                                    class="fa-fw fa fa-step-backward"></i></a></li>

                        <li>
                            <a href="#" class="list__link" :class="{ 'disabled': this.playerEvents.isLoading }" v-show="! this.playerEvents.isLoading && this.playerEvents.isPaused" @click.prevent="onPlayButtonClick"><i class="fa-fw fa fa-play"></i></a>
                            <a href="#" class="list__link" v-show="! this.playerEvents.isLoading && this.playerEvents.isPlaying" @click.prevent="onPauseButtonClick"><i class="fa-fw fa fa-pause"></i></a>                        
                            <a href="#" class="list__link" v-show="this.playerEvents.isLoading"><i class="fa-fw fa fa-cog fa-spin"></i></a>                        
                        </li>

                        <li><a href="#" class="list__link" :class="{ 'disabled': this.playerEvents.isLoading }" @click.prevent="onNextTrackButtonClick"><i
                                    class="fa-fw fa fa-step-forward"></i></a></li>
                    </ul>
                    </div>
                </div>

                <div class="field">
                    <div class="control has-icons-left has-icons-right">
                        <span class="icon is-left" style="height: 1em;">
                            <i @click.prevent="onToggleMute" class="is-clickable fas fw" :class="{ 'fa-volume-mute': volume == 0, 'fa-volume-off': volume > 0 && volume <= 10, 'fa-volume-down': volume > 0 && volume <= 50, 'fa-volume-up': volume > 50 }" style="color: #d30320;"></i>
                        </span>
                        <input style="padding-left: 3.5em; padding-right: 3.5em;"
                            class="slider is-fullwidth is-small is-circle" step="1" min="0" max="100" type="range"
                            v-model.number="volume">
                            <span class="icon is-right" style="height: 1em;">
                            <small>{{ volume }}%</small>
                        </span>
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
    name: 'spieldose-component-player',
    template: template(),
    data: function () {
        return ({
            // audio can not auto-play without this
            hasPreviousUserInteractions: false,
            audioElement: null,
            audioCanBePlayed: false,
            playerEvents: {
                isLoading: false,
                isPaused: true,
                isPlaying: false
            },
            oldVolume: 0,
            volume: 8,
            position: 0,
            audioElementMotion: null,
            currentTime: "00:00",
            duration: "00:00",
            audioElementMotionMode: 3,
            coverURL: null,            
            showAnalyzer: false,
            customVinyl: true

        });
    },
    props: [
        'track', 'animations'
    ],
    computed: {
        trackId: function () {
            return (this.track ? this.track.id : null);
        }
    },
    watch: {
        customVinyl: function(newValue) {
            const url = "/api2/track/thumbnail/" + (newValue ? 'small': 'normal') + "/" + this.track.id;
            let img = new Image();
            img.src = url
            img.onload = () => {
                this.coverURL = url;
            }
            img.onerror = () => {
                this.coverURL = null;
            }
        },
        trackId: function (newValue, oldValue) {
            this.audioCanBePlayed = false;
            this.playerEvents.isPaused = true;
            this.playerEvents.isPlaying = false;
            if (newValue) {
                console.log("Buffering audio start");
                this.playerEvents.isLoading = true;
                this.coverURL = null;
                if (this.audioElement) {
                    this.audioElement.src = "/api2/file/" + this.track.id;
                    //const url = this.track.thumbnailURL;
                    const url = "/api2/track/thumbnail/" + (this.customVinyl ? 'small': 'normal') + "/" + this.track.id;
                    let img = new Image();
                    img.src = url
                    img.onload = () => {
                        this.coverURL = url;
                    }
                    img.onerror = () => {
                        this.coverURL = null;
                    }
                    this.audioElement.load();
                    //if (newValue && oldValue) {
                    //this.play();
                }
                //}
            }
        },
        audioCanBePlayed: function (newValue) {
            if (newValue) {
                this.play();
            }
        },
        volume: function (newValue, oldValue) {
            this.oldVolume = oldValue;
            this.setVolume(newValue / 100);
        }
    },
    created: function () {
        /*        
        console.log(this.track);
        console.log(this.aa);
        console.log(this.animations);
        */
    },
    mounted: function () {
        console.debug('Creating audio element');
        this.audioElement = document.getElementById('audio');
        console.debug('Setting audio volume at ' + this.volume);
        this.setVolume(this.volume / 100);
        console.debug('Setting audio available to play event');
                
        this.audioElement.addEventListener('canplay', (event) => {
            console.log("Buffering audio end");
            console.debug('Audio can be played');
            this.audioCanBePlayed = true;
            this.playerEvents.isLoading = false;
            this.$bus.emit('playerEvent', this.playerEvents);
        });
        this.audioElement.addEventListener('pause', (event) => {
            console.debug('Audio is paused');
            this.playerEvents.isPaused = true;
            this.playerEvents.isPlaying = false;
            this.$bus.emit('playerEvent', this.playerEvents);
        });
        this.audioElement.addEventListener('play', (event) => {
            console.debug('Audio is playing1');
            //this.playerEvents.isPaused = false;
            //this.playerEvents.isPlaying = true;
            this.$bus.emit('playerEvent', this.playerEvents);
        });
        this.audioElement.addEventListener('playing', (event) => {
            console.debug('Audio is playing2');
            this.playerEvents.isPaused = false;
            this.playerEvents.isPlaying = true;
            this.$bus.emit('playerEvent', this.playerEvents);
        });
        this.audioElement.addEventListener('ended', (event) => {
            console.debug('Audio is ended');
            //this.playerEvents.isPaused = false;
            //this.playerEvents.isPlaying = true;
            this.$bus.emit('playerEvent', this.playerEvents);
        });

        this.audioElement.addEventListener('error', (event) => {
            console.debug('Audio loading error');
            console.log(event);
            /*
            this.playerEvents.isPaused = true;
            this.playerEvents.isPlaying = false;
            this.playerEvents.isLoading = false;
            */
            this.$bus.emit('playerEvent', this.playerEvents);
        });

        this.audioElement.addEventListener('timeupdate', (event) => {
            //console.debug('Audio timeupdate');
            const currentProgress = this.audioElement.currentTime / this.audioElement.duration;
            this.duration = this.formatSecondsAsTime(Math.floor(this.audioElement.duration).toString());
            this.currentTime = this.formatSecondsAsTime(Math.floor(this.audioElement.currentTime).toString());
            if (!isNaN(currentProgress)) {
                this.position = currentProgress.toFixed(2);
            } else {
                this.position = 0;
            }
            //this.currentPlayedSeconds = Math.floor(aa.currentTime).toString();            
            //console.log(this.audioElement.currentTime);
            //console.debug(event);
            //this.playerEvents.isPaused = false;
            //this.playerEvents.isPlaying = true;
            this.$bus.emit('playerEvent', this.playerEvents);
        });        
        
        /*
        
        if (this.showAnalyzer) {
            this.audioElementMotion = new AudioMotionAnalyzer(
                document.getElementById('container'),
                {
                    source: this.audioElement,
                    mode: this.audioElementMotionMode,
                    height: 40,
                    ledBars: false,
                    showScaleX: false,
                    showScaleY: false,
                    stereo: false,
                    splitGradient: false
                }
            );
        }*/
    },
    methods: {
        /**
         * set audio player volume
         * @param {*} volume (0 = muted, 1 = max volume)
         */
        setVolume: function (volume) {
            if (volume >= 0 && volume <= 1) {
                if (this.audioElement) {
                    this.audioElement.volume = volume;
                } else {
                    console.error("Audio element not mounted");
                }
            } else {
                console.error("Error setting volume, invalid value:" + volume);
            }
        },
        play: function () {
            if (! this.playerEvents.isPaused) {
                console.debug('Audio is playing, stopping...');
                this.audioElement.pause();
            }
            if (this.hasPreviousUserInteractions) {
                console.debug('Playing audio');
                this.audioElement.play();
            } else {
                console.debug('No previous user interactions found, browser will deny play');
            }
        },   
        pause: function () {
            if (this.playerEvents.isPlaying) {
                console.debug('Audio is playing, stopping...');
                this.audioElement.pause();
            }
        },     

        onToggleMute: function() {
            if (this.volume != 0) {
                this.oldVolume = this.volume;
                this.volume = 0;
            } else {
                this.volume = this.oldVolume;   
            }
            this.setVolume(this.volume / 100);
        },
        onPlayButtonClick: function () {
            this.hasPreviousUserInteractions = true;
            this.play();
        },
        onPauseButtonClick: function () {
            this.hasPreviousUserInteractions = true;
            this.pause();
        },

        onPreviousTrackButtonClick: function () {
            this.hasPreviousUserInteractions = true;
            this.pause();
            this.$bus.emit('onPreviousTrack');
        },
        onNextTrackButtonClick: function () {
            this.hasPreviousUserInteractions = true;
            this.pause();
            this.$bus.emit('onNextTrack');
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
        onChangeaudioElementMotionAnalyzerMode: function () {
            if (this.showAnalyzer) {
                this.audioElementMotionMode++;
                if (this.audioElementMotionMode > 8) {
                    this.audioElementMotionMode = 0;
                }
                this.audioElementMotion.setOptions({ mode: this.audioElementMotionMode });
            }
        }
    }
};
