let playerNavBar = (function () {
    "use strict";

    const template = function () {
        return `
            <nav class="navbar is-light is-fixed-bottom is-unselectable" role="navigation" aria-label="main navigation" v-show="showPlayerNavBar">
                <div class="navbar-menu">
                    <div class="navbar-start">
                        <div class="navbar-item has-dropdown has-dropdown-up is-hoverable">
                            <a class="navbar-link">
                                <span class="icon"><i class="fas fa-bars"></i></span>
                                <span>Menu</span>
                            </a>
                            <div class="navbar-dropdown">
                                <a v-on:click.prevent="$router.push({ name: 'dashboard' })" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'dashboard'}">
                                <span class="panel-icon"><i class="fas fa-chart-line"></i></span>
                                dashboard
                                </a>
                                <a v-on:click.prevent="$router.push({ name: 'nowPlaying' })" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'nowPlaying'}">
                                <span class="panel-icon"><i class="fas fa-headphones"></i></span>
                                current playlist
                                </a>
                                <a v-on:click.prevent="$router.push({ name: 'search' })" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'search'}">
                                <span class="panel-icon"><i class="fas fa-search"></i></span>
                                search
                                </a>
                                <a v-on:click.prevent="$router.push({ name: 'artists' })" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'artists'}">
                                <span class="panel-icon"><i class="fas fa-user"></i></span>
                                browse artists
                                </a>
                                <a v-on:click.prevent="$router.push({ name: 'albums' })" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'albums'}">
                                <span class="panel-icon"><i class="fas fa-circle"></i></span>
                                browse albums
                                </a>
                                <a v-on:click.prevent="$router.push({ name: 'paths' })" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'paths'}">
                                <span class="panel-icon"><i class="fas fa-folder-open"></i></span>
                                browse paths
                                </a>
                                <a v-on:click.prevent="$router.push({ name: 'playlists' })" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'playlists'}">
                                <span class="panel-icon"><i class="fas fa-list-alt"></i></span>
                                browse playlists
                                </a>
                                <a v-on:click.prevent="signout();" class="navbar-item">
                                <span class="panel-icon"><i class="fas fa-sign-out-alt"></i></span>
                                signout
                                </a>
                            </div>
                        </div>
                        <div class="navbar-item">
                            <img v-bind:src="coverSrc" style="width: 64px; max-height: 64px;">
                        </div>
                        <div class="navbar-item player-buttons">
                            <span title="go to previous track" id="btn-previous" v-on:click.prevent="playerData.playPreviousTrack();" class="icon"><i class="fas fa-2x fa-step-backward"></i></span>
                            <span title="pause track" id="btn-pause" v-on:click.prevent="playerData.pause();" v-if="playerData.isPlaying" class="icon"><i class="fas fa-2x fa-pause"></i></span>
                            <span title="play track" id="btn-play" v-on:click.prevent="playerData.play();" v-else class="icon"><i class="fas fa-2x fa-play"></i></span>
                            <span title="go to next track" id="btn-next" v-on:click.prevent="playerData.playNextTrack();" class="icon"><i class="fas fa-2x fa-step-forward"></i></span>
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
                                    <span id="song-current-time" class="level-item has-text-grey">{{ currentPlayedSeconds | formatSeconds }}</span>
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

    /* small fixed navbar player controls component */
    let module = Vue.component('player-navbar', {
        template: template(),
        mixins: [mixinPlayer],
        data: function () {
            return ({
                showPlayerNavBar: false,
                playerVisibilityObserver: null
            });
        },
        created: function () {
            let self = this;
            bus.$on("showPlayerNavBar", function () {
                const rootEl = document.documentElement;
                rootEl.classList.add('has-navbar-fixed-bottom');
                self.showPlayerNavBar = true;
            });
            bus.$on("hidePlayerNavbar", function () {
                const rootEl = document.documentElement;
                rootEl.classList.remove('has-navbar-fixed-bottom');
                self.showPlayerNavBar = false;
            });
        },
        computed: {
            coverSrc: function () {
                if (this.playerData.currentTrack.track && this.playerData.currentTrack.track.image) {
                    if (this.playerData.currentTrack.track.image.indexOf("http") == 0) {
                        return ("api/thumbnail?url=" + this.playerData.currentTrack.track.image);
                    } else {
                        return ("api/thumbnail?hash=" + this.playerData.currentTrack.track.image);
                    }
                } else {
                    return ('images/vinyl.png');
                }
            }
        }, methods: {
            signout: function (e) {
                bus.$emit("signOut");
            }
        }
    });

    return (module);
})();