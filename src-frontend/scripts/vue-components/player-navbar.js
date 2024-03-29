import { bus } from '../bus.js';

const template = function () {
    return `
        <nav class="navbar is-light is-fixed-bottom is-unselectable" role="navigation" aria-label="main navigation" v-show="showPlayerNavBar">
            <div class="navbar-menu">
                <div class="navbar-start">
                    <div class="navbar-item has-dropdown has-dropdown-up is-hoverable">
                        <a class="navbar-link">
                            <span class="icon"><i class="fas fa-bars"></i></span>
                            <span>{{ $t('menu.labels.header') }}</span>
                        </a>
                        <div class="navbar-dropdown">
                            <a v-on:click.prevent="$router.push({ name: 'dashboard' })" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'dashboard'}">
                            <span class="panel-icon"><i class="fas fa-chart-line"></i></span>
                            {{ $t('menu.labels.dashboard') }}
                            </a>
                            <a v-on:click.prevent="$router.push({ name: 'nowPlaying' })" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'nowPlaying'}">
                            <span class="panel-icon"><i class="fas fa-headphones"></i></span>
                            {{ $t('menu.labels.currentPlaylist') }}
                            </a>
                            <a v-on:click.prevent="$router.push({ name: 'search' })" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'search'}">
                            <span class="panel-icon"><i class="fas fa-search"></i></span>
                            {{ $t('menu.labels.search') }}
                            </a>
                            <a v-on:click.prevent="$router.push({ name: 'artists' })" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'artists'}">
                            <span class="panel-icon"><i class="fas fa-user"></i></span>
                            {{ $t('menu.labels.browseArtists') }}
                            </a>
                            <a v-on:click.prevent="$router.push({ name: 'albums' })" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'albums'}">
                            <span class="panel-icon"><i class="fas fa-circle"></i></span>
                            {{ $t('menu.labels.browseAlbums') }}
                            </a>
                            <a v-on:click.prevent="$router.push({ name: 'paths' })" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'paths'}">
                            <span class="panel-icon"><i class="fas fa-folder-open"></i></span>
                            {{ $t('menu.labels.browsePaths') }}
                            </a>
                            <a v-on:click.prevent="$router.push({ name: 'playlists' })" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'playlists'}">
                            <span class="panel-icon"><i class="fas fa-list-alt"></i></span>
                            {{ $t('menu.labels.browsePlaylists') }}
                            </a>
                            <a v-on:click.prevent="signout();" class="navbar-item">
                            <span class="panel-icon"><i class="fas fa-sign-out-alt"></i></span>
                            {{ $t('menu.labels.signOut') }}
                            </a>
                        </div>
                    </div>
                    <div class="navbar-item">
                        <img v-bind:src="coverSrc" style="width: 64px; max-height: 64px;">
                    </div>
                    <div class="navbar-item player-buttons">
                        <span v-bind:title="$t('player.buttons.previousTrackHint')" id="btn-previous" v-on:click.prevent="$audioplayer.currentPlaylist.playPrevious();" class="icon"><i class="fas fa-2x fa-step-backward"></i></span>
                        <span v-bind:title="$t('player.buttons.pauseTrackHint')" id="btn-pause" v-on:click.prevent="$audioplayer.playback.pause();" v-if="$audioplayer.isPlaying" class="icon"><i class="fas fa-2x fa-pause"></i></span>
                        <span v-bind:title="$t('player.buttons.playTrackHint')" id="btn-play" v-on:click.prevent="$audioplayer.playback.play();" v-else class="icon"><i class="fas fa-2x fa-play"></i></span>
                        <span v-bind:title="$t('player.buttons.nextTrackHint')" id="btn-next" v-on:click.prevent="$audioplayer.currentPlaylist.playNext();" class="icon"><i class="fas fa-2x fa-step-forward"></i></span>
                    </div>
                    <div class="navbar-item">
                        <div>
                            <p class="title is-3">{{ nowPlayingTitle }}</p>
                            <p class="subtitle is-5">{{nowPlayingArtist }}</p>
                        </div>
                    </div>
                    <!--
                    <div class="navbar-item">
                        <nav class="level is-marginless">
                            <div class="level-left">
                                <span id="song-current-time" class="level-item has-text-grey">{{ currentPlayedSeconds }}</span>
                            </div>
                            <div class="level-item">
                                <input id="song-played-progress" class="is-pulled-left" type="range" v-model="songProgress" min="0" max="1" step="0.01" />
                            </div>
                            <div class="level-right">
                                <span id="song-duration" class="level-item has-text-grey">{{ nowPlayingLength }}</span>
                            </div>
                        </nav>
                    </div>
                    -->
                </div>
            </div>
        </nav>
    `;
};

export default {
    name: 'player-navbar',
    template: template(),
    data: function () {
        return ({
            showPlayerNavBar: false,
            playerVisibilityObserver: null
        });
    },
    created: function () {
        bus.on('showPlayerNavBar',  () => {
            const rootEl = document.documentElement;
            rootEl.classList.add('has-navbar-fixed-bottom');
            this.showPlayerNavBar = true;
        });
        bus.on('hidePlayerNavbar',  () => {
            const rootEl = document.documentElement;
            rootEl.classList.remove('has-navbar-fixed-bottom');
            this.showPlayerNavBar = false;
        });
    },
    computed: {
        coverSrc: function () {
            if (this.$audioplayer.currentTrack.track && this.$audioplayer.currentTrack.track.image) {
                if (this.$audioplayer.currentTrack.track.image.indexOf('http') == 0) {
                    return ('api/thumbnail?url=' + encodeURIComponent(this.$audioplayer.currentTrack.track.image));
                } else {
                    return ('api/thumbnail?hash=' + this.$audioplayer.currentTrack.track.image);
                }
            } else {
                return ('images/vinyl.png');
            }
        }
    },
    methods: {
        signout: function (e) {
            bus.emit('signOut');
        }
    }
}