import{bus}from"../bus.js";import{mixinPlayer}from"../mixins.js";const template=function(){return'\n        <nav class="navbar is-light is-fixed-bottom is-unselectable" role="navigation" aria-label="main navigation" v-show="showPlayerNavBar">\n            <div class="navbar-menu">\n                <div class="navbar-start">\n                    <div class="navbar-item has-dropdown has-dropdown-up is-hoverable">\n                        <a class="navbar-link">\n                            <span class="icon"><i class="fas fa-bars"></i></span>\n                            <span>{{ $t(\'menu.labels.header\') }}</span>\n                        </a>\n                        <div class="navbar-dropdown">\n                            <a v-on:click.prevent="$router.push({ name: \'dashboard\' })" class="navbar-item" v-bind:class="{ \'is-active\': $route.name == \'dashboard\'}">\n                            <span class="panel-icon"><i class="fas fa-chart-line"></i></span>\n                            {{ $t(\'menu.labels.dashboard\') }}\n                            </a>\n                            <a v-on:click.prevent="$router.push({ name: \'nowPlaying\' })" class="navbar-item" v-bind:class="{ \'is-active\': $route.name == \'nowPlaying\'}">\n                            <span class="panel-icon"><i class="fas fa-headphones"></i></span>\n                            {{ $t(\'menu.labels.currentPlaylist\') }}\n                            </a>\n                            <a v-on:click.prevent="$router.push({ name: \'search\' })" class="navbar-item" v-bind:class="{ \'is-active\': $route.name == \'search\'}">\n                            <span class="panel-icon"><i class="fas fa-search"></i></span>\n                            {{ $t(\'menu.labels.search\') }}\n                            </a>\n                            <a v-on:click.prevent="$router.push({ name: \'artists\' })" class="navbar-item" v-bind:class="{ \'is-active\': $route.name == \'artists\'}">\n                            <span class="panel-icon"><i class="fas fa-user"></i></span>\n                            {{ $t(\'menu.labels.browseArtists\') }}\n                            </a>\n                            <a v-on:click.prevent="$router.push({ name: \'albums\' })" class="navbar-item" v-bind:class="{ \'is-active\': $route.name == \'albums\'}">\n                            <span class="panel-icon"><i class="fas fa-circle"></i></span>\n                            {{ $t(\'menu.labels.browseAlbums\') }}\n                            </a>\n                            <a v-on:click.prevent="$router.push({ name: \'paths\' })" class="navbar-item" v-bind:class="{ \'is-active\': $route.name == \'paths\'}">\n                            <span class="panel-icon"><i class="fas fa-folder-open"></i></span>\n                            {{ $t(\'menu.labels.browsePaths\') }}\n                            </a>\n                            <a v-on:click.prevent="$router.push({ name: \'playlists\' })" class="navbar-item" v-bind:class="{ \'is-active\': $route.name == \'playlists\'}">\n                            <span class="panel-icon"><i class="fas fa-list-alt"></i></span>\n                            {{ $t(\'menu.labels.browsePlaylists\') }}\n                            </a>\n                            <a v-on:click.prevent="signout();" class="navbar-item">\n                            <span class="panel-icon"><i class="fas fa-sign-out-alt"></i></span>\n                            {{ $t(\'menu.labels.signOut\') }}\n                            </a>\n                        </div>\n                    </div>\n                    <div class="navbar-item">\n                        <img v-bind:src="coverSrc" style="width: 64px; max-height: 64px;">\n                    </div>\n                    <div class="navbar-item player-buttons">\n                        <span v-bind:title="$t(\'player.buttons.previousTrackHint\')" id="btn-previous" v-on:click.prevent="playerData.currentPlaylist.playPrevious();" class="icon"><i class="fas fa-2x fa-step-backward"></i></span>\n                        <span v-bind:title="$t(\'player.buttons.pauseTrackHint\')" id="btn-pause" v-on:click.prevent="playerData.playback.pause();" v-if="playerData.isPlaying" class="icon"><i class="fas fa-2x fa-pause"></i></span>\n                        <span v-bind:title="$t(\'player.buttons.playTrackHint\')" id="btn-play" v-on:click.prevent="playerData.playback.play();" v-else class="icon"><i class="fas fa-2x fa-play"></i></span>\n                        <span v-bind:title="$t(\'player.buttons.nextTrackHint\')" id="btn-next" v-on:click.prevent="playerData.currentPlaylist.playNext();" class="icon"><i class="fas fa-2x fa-step-forward"></i></span>\n                    </div>\n                    <div class="navbar-item">\n                        <div>\n                            <p class="title is-3">{{ nowPlayingTitle }}</p>\n                            <p class="subtitle is-5">{{nowPlayingArtist }}</p>\n                        </div>\n                    </div>\n                    \x3c!--\n                    <div class="navbar-item">\n                        <nav class="level is-marginless">\n                            <div class="level-left">\n                                <span id="song-current-time" class="level-item has-text-grey">{{ currentPlayedSeconds | formatSeconds }}</span>\n                            </div>\n                            <div class="level-item">\n                                <input id="song-played-progress" class="is-pulled-left" type="range" v-model="songProgress" min="0" max="1" step="0.01" />\n                            </div>\n                            <div class="level-right">\n                                <span id="song-duration" class="level-item has-text-grey">{{ nowPlayingLength }}</span>\n                            </div>\n                        </nav>\n                    </div>\n                    --\x3e\n                </div>\n            </div>\n        </nav>\n    '};export default{name:"player-navbar",template:'\n        <nav class="navbar is-light is-fixed-bottom is-unselectable" role="navigation" aria-label="main navigation" v-show="showPlayerNavBar">\n            <div class="navbar-menu">\n                <div class="navbar-start">\n                    <div class="navbar-item has-dropdown has-dropdown-up is-hoverable">\n                        <a class="navbar-link">\n                            <span class="icon"><i class="fas fa-bars"></i></span>\n                            <span>{{ $t(\'menu.labels.header\') }}</span>\n                        </a>\n                        <div class="navbar-dropdown">\n                            <a v-on:click.prevent="$router.push({ name: \'dashboard\' })" class="navbar-item" v-bind:class="{ \'is-active\': $route.name == \'dashboard\'}">\n                            <span class="panel-icon"><i class="fas fa-chart-line"></i></span>\n                            {{ $t(\'menu.labels.dashboard\') }}\n                            </a>\n                            <a v-on:click.prevent="$router.push({ name: \'nowPlaying\' })" class="navbar-item" v-bind:class="{ \'is-active\': $route.name == \'nowPlaying\'}">\n                            <span class="panel-icon"><i class="fas fa-headphones"></i></span>\n                            {{ $t(\'menu.labels.currentPlaylist\') }}\n                            </a>\n                            <a v-on:click.prevent="$router.push({ name: \'search\' })" class="navbar-item" v-bind:class="{ \'is-active\': $route.name == \'search\'}">\n                            <span class="panel-icon"><i class="fas fa-search"></i></span>\n                            {{ $t(\'menu.labels.search\') }}\n                            </a>\n                            <a v-on:click.prevent="$router.push({ name: \'artists\' })" class="navbar-item" v-bind:class="{ \'is-active\': $route.name == \'artists\'}">\n                            <span class="panel-icon"><i class="fas fa-user"></i></span>\n                            {{ $t(\'menu.labels.browseArtists\') }}\n                            </a>\n                            <a v-on:click.prevent="$router.push({ name: \'albums\' })" class="navbar-item" v-bind:class="{ \'is-active\': $route.name == \'albums\'}">\n                            <span class="panel-icon"><i class="fas fa-circle"></i></span>\n                            {{ $t(\'menu.labels.browseAlbums\') }}\n                            </a>\n                            <a v-on:click.prevent="$router.push({ name: \'paths\' })" class="navbar-item" v-bind:class="{ \'is-active\': $route.name == \'paths\'}">\n                            <span class="panel-icon"><i class="fas fa-folder-open"></i></span>\n                            {{ $t(\'menu.labels.browsePaths\') }}\n                            </a>\n                            <a v-on:click.prevent="$router.push({ name: \'playlists\' })" class="navbar-item" v-bind:class="{ \'is-active\': $route.name == \'playlists\'}">\n                            <span class="panel-icon"><i class="fas fa-list-alt"></i></span>\n                            {{ $t(\'menu.labels.browsePlaylists\') }}\n                            </a>\n                            <a v-on:click.prevent="signout();" class="navbar-item">\n                            <span class="panel-icon"><i class="fas fa-sign-out-alt"></i></span>\n                            {{ $t(\'menu.labels.signOut\') }}\n                            </a>\n                        </div>\n                    </div>\n                    <div class="navbar-item">\n                        <img v-bind:src="coverSrc" style="width: 64px; max-height: 64px;">\n                    </div>\n                    <div class="navbar-item player-buttons">\n                        <span v-bind:title="$t(\'player.buttons.previousTrackHint\')" id="btn-previous" v-on:click.prevent="playerData.currentPlaylist.playPrevious();" class="icon"><i class="fas fa-2x fa-step-backward"></i></span>\n                        <span v-bind:title="$t(\'player.buttons.pauseTrackHint\')" id="btn-pause" v-on:click.prevent="playerData.playback.pause();" v-if="playerData.isPlaying" class="icon"><i class="fas fa-2x fa-pause"></i></span>\n                        <span v-bind:title="$t(\'player.buttons.playTrackHint\')" id="btn-play" v-on:click.prevent="playerData.playback.play();" v-else class="icon"><i class="fas fa-2x fa-play"></i></span>\n                        <span v-bind:title="$t(\'player.buttons.nextTrackHint\')" id="btn-next" v-on:click.prevent="playerData.currentPlaylist.playNext();" class="icon"><i class="fas fa-2x fa-step-forward"></i></span>\n                    </div>\n                    <div class="navbar-item">\n                        <div>\n                            <p class="title is-3">{{ nowPlayingTitle }}</p>\n                            <p class="subtitle is-5">{{nowPlayingArtist }}</p>\n                        </div>\n                    </div>\n                    \x3c!--\n                    <div class="navbar-item">\n                        <nav class="level is-marginless">\n                            <div class="level-left">\n                                <span id="song-current-time" class="level-item has-text-grey">{{ currentPlayedSeconds | formatSeconds }}</span>\n                            </div>\n                            <div class="level-item">\n                                <input id="song-played-progress" class="is-pulled-left" type="range" v-model="songProgress" min="0" max="1" step="0.01" />\n                            </div>\n                            <div class="level-right">\n                                <span id="song-duration" class="level-item has-text-grey">{{ nowPlayingLength }}</span>\n                            </div>\n                        </nav>\n                    </div>\n                    --\x3e\n                </div>\n            </div>\n        </nav>\n    ',mixins:[mixinPlayer],data:function(){return{showPlayerNavBar:!1,playerVisibilityObserver:null}},created:function(){bus.$on("showPlayerNavBar",(function(){document.documentElement.classList.add("has-navbar-fixed-bottom"),this.showPlayerNavBar=!0})),bus.$on("hidePlayerNavbar",(function(){document.documentElement.classList.remove("has-navbar-fixed-bottom"),this.showPlayerNavBar=!1}))},computed:{coverSrc:function(){return this.playerData.currentTrack.track&&this.playerData.currentTrack.track.image?0==this.playerData.currentTrack.track.image.indexOf("http")?"api/thumbnail?url="+encodeURIComponent(this.playerData.currentTrack.track.image):"api/thumbnail?hash="+this.playerData.currentTrack.track.image:"images/vinyl.png"}},methods:{signout:function(a){bus.$emit("signOut")}}};