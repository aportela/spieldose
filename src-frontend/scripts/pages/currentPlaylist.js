import { nextTick } from "vue";
import bus from "../plugins/bus";
import player from "../vue-components/player";

const template = function () {
    return `
        <div class="field has-addons">
            <div class="control has-icons-left is-expanded is-small" :class="{ 'is-loading': loading }">
                <input class="input is-small" type="text" placeholder="Search" v-model.trim="searchQuery"
                    v-on:keyup.enter="onSearch" :disabled="loading">
                <span class="icon is-small is-left">
                    <i class="fas fa-search"></i>
                </span>
                <span class="icon is-small is-right">
                    <i class="fas fa-loading"></i>
                </span>
            </div>
            <div class="control">
                <button class="button is-small is-pink" @click.prevent="onSearch" :disabled="loading">Search</button>
            </div>
        </div>

        <div class="field has-addons">
            <p class="control">
                <button class="button is-small" @click.prevent="onClearPlaylist" :disabled="loading || playerEvent.isLoading">
                <span class="icon is-small">
                    <i class="fa-solid fa-xmark"></i>
                </span>
                <span>Clear</span>
                </button>
            </p>
            <p class="control">
                <button class="button is-small" @click.prevent="loadTracks" :disabled="loading || playerEvent.isLoading">
                <span class="icon is-small">
                    <i class="fa-solid fa-rotate"></i>
                </span>
                <span>Random</span>
                </button>
            </p>
            <p class="control">
                <button class="button is-small" @click.prevent="onPreviousTrack" :disabled="loading || playerEvent.isLoading || currentTrackIndex < 1">
                    <span class="icon is-small">
                        <i class="fa-fw fa fa-step-backward"></i>
                    </span>
                    <span>Previous</span>
                </button>
            </p>
            <p class="control">
                <button class="button is-small" @click.prevent="onTogglePlay" disabled>
                    <span class="icon is-small">
                        <i class="fa-fw fa-solid mr-2" :class="{ 'fa-play': ! playerEvent.isLoading && playerEvent.isPaused, 'fa-pause': ! playerEvent.isLoading && playerEvent.isPlaying, 'fa-cog fa-spin': playerEvent.isLoading }"></i>
                    </span>
                    <span v-if="playerEvent.isLoading">Loading</span>
                    <span v-else-if="playerEvent.isPlaying">Pause</span>
                    <span v-else>Play</span>
                </button>
            </p>
            <p class="control">
                <button class="button is-small" @click.prevent="onNextTrack" :disabled="loading || playerEvent.isLoading || currentTrackIndex >= tracks.length - 1">
                    <span class="icon is-small">
                        <i class="fa-fw fa fa-step-forward"></i>
                    </span>
                    <span>Next</span>
                </button>
            </p>
            <p class="control">
                <button class="button is-small" disabled>
                <span class="icon is-small">
                    <i class="fa-solid fa-save"></i>
                </span>
                <span>Save</span>
                </button>
            </p>
        </div>

        <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth" style="font-size: 1rem;">
            <thead>
                <tr>
                    <th class="has-text-right">Index</th>
                    <th>Title</th>
                    <th>Artist</th>
                    <th>Album Artist</th>
                    <th>Album</th>
                    <th class="has-text-right">Album Track nº</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="track,index in tracks" :key="index" class="is-clickable whitespace-nowrap" @click.prevent="onChangeCurrentTrackIndex(index)"
                    :class="{ 'is-selected-pink': currentTrack.id == track.id } ">
                    <td class="has-text-right"><i class="fa-fw fa-solid mr-2" :class="{ 'fa-play': ! playerEvent.isLoading && playerEvent.isPaused, 'fa-pause': ! playerEvent.isLoading && playerEvent.isPlaying, 'fa-cog fa-spin': playerEvent.isLoading }" v-if="currentTrack.id == track.id"></i> {{ index + 1 }}/{{ tracks.length }}</td>
                    <td>{{ track.title }}</td>
                    <td>{{ track.artist }} <router-link v-if="track.artist" :to="{ name: 'artistPage', params: { name: track.artist } }"><i class="fas fa-link ml-1"></i></router-link></td>

                    <td>{{ track.albumArtist }} <span class="is-clickable" v-if="track.albumArtist" @click.prevent="loadTracks('', '', track.albumArtist, '');"><i class="fas fa-link ml-1"></i></span></td>
                    <td>{{ track.album }}<span class="is-clickable" v-if="track.album" @click.prevent="loadTracks('', '', track.albumArtist, track.album);"><i class="fas fa-link ml-1"></i></span></td>
                    <td class="has-text-right">{{ track.trackNumber }}</td>
                    <td>{{ track.year }}</td>
                </tr>
            </tbody>
        </table>
    `;
};

export default {
    name: 'spieldose-page-current-playlist',
    template: template(),
    data: function () {
        return ({
            loading: false,
            //tracks: [],
            //currentTrackIndex: -1,
            searchQuery: null,
            playerEvent: {}
        });
    },
    computed: {
        tracks: function () {
            return (this.$player.currentPlaylist.tracks);
        },
        currentTrackIndex: function () {
            return (this.$player.currentPlaylist.trackIndex);
        },
        currentTrack: function () {
            if (this.tracks && this.tracks.length > 0 && this.currentTrackIndex >= 0) {
                return (this.tracks[this.currentTrackIndex]);
            } else {
                //console.log("returning null");
                return ({});
            }
        }
    },
    watch: {
        currentTrackIndex: function (newValue, oldValue) {
            this.$localStorage.set('currentPlaylistTrackIndex', newValue);
            this.$bus.emit('onTrackChanged', { track: this.currentTrack });
        }
    },
    created: function () {
        this.$bus.on('onPreviousTrack', () => { this.onPreviousTrack(); });
        this.$bus.on('onNextTrack', () => { this.onNextTrack(); });
        this.$bus.on('playerEvent', (playerEvent) => {
            this.playerEvent = playerEvent;
        });
        this.$bus.on('endTrack', (trackId) => {
            this.onIncreaseTrackPlayCount(trackId);
        });
        this.$bus.on('loveTrack', (trackId) => {
            this.onLoveTrack(trackId);
        });
        this.$bus.on('unLoveTrack', (trackId) => {
            this.onUnLoveTrack(trackId);
        });
    },
    mounted: function () {
        /*
        const savedPlaylist = this.$localStorage.get('currentPlaylist');
        const savedPlaylistIndex = this.$localStorage.get('currentPlaylistTrackIndex');
        if (savedPlaylist && savedPlaylist.length > 0 && savedPlaylistIndex >= 0 && savedPlaylistIndex < savedPlaylist.length) {
            this.$nextTick(() => {
                this.tracks = savedPlaylist;
                this.currentTrackIndex = savedPlaylistIndex;
            });
        } else {
            this.loadTracks();
        }
        */
    },
    methods: {
        loadTracks: function (query, artist, albumArtist, album) {
            if (artist || album) {
                this.searchQuery = null;
            }
            this.loading = true;
            this.tracks = [];
            this.currentTrackIndex = -1;
            this.$api.track.search(this.searchQuery, artist, albumArtist, album).then(success => {
                this.$player.replaceCurrentPlaylist(success.data.tracks);
                this.loading = false;
            }).catch(error => {
                console.log(error);
                switch (error.response.status) {
                    case 400:
                        if (error.isFieldInvalid('email')) {
                            this.validator.setInvalid('signUpEmail', this.$t('commonErrors.invalidAPIParam'));
                            this.$nextTick(() => this.$refs.signUpEmail.focus());
                        } else if (error.isFieldInvalid('password')) {
                            this.validator.setInvalid('signUpPassword', this.$t('commonErrors.invalidAPIParam'));
                            this.$nextTick(() => this.$refs.signUpPassword.focus());
                        } else {
                            this.setAPIError(error.getApiErrorData());
                        }
                        break;
                    case 409:
                        this.validator.setInvalid('signUpEmail', this.$t('signUp.errorMessages.emailAlreadyUsed'));
                        this.$nextTick(() => this.$refs.signUpEmail.focus());
                        break;
                    default:
                        //this.setAPIError(error.getApiErrorData());
                        break;
                }
                this.loading = false;
            });
            /*
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
            */
        },
        onIncreaseTrackPlayCount: function (trackId) {
            this.$api.track.increasePlayCount(trackId).then(success => {
                // TODO
            }).catch(error => {
                // TODO
            });
        },
        onLoveTrack: function (trackId) {
            this.$api.track.love(trackId).then(success => {
                this.tracks.find((track) => track.id == trackId).loved = true;
                this.$localStorage.set('currentPlaylist', this.tracks);
                // TODO
            }).catch(error => {
                // TODO
            });
        },
        onUnLoveTrack: function (trackId) {
            this.$api.track.unLove(trackId).then(success => {
                this.tracks.find((track) => track.id == trackId).loved = false;
                this.$localStorage.set('currentPlaylist', this.tracks);
                // TODO
            }).catch(error => {
                // TODO
            });
        },
        onSearch: function () {
            this.loadTracks(this.searchQuery);
        },
        onPreviousTrack: function () {
            this.$player.onPreviousTrack();
        },
        onNextTrack: function () {
            this.$player.onNextTrack();
        },
        onTogglePlay: function () {
            this.$player.hasPreviousUserInteractions = true;
        },
        onChangeCurrentTrackIndex: function (index) {
            this.$player.onChangeCurrentTrackIndex(index);
        },
        onClearPlaylist: function () {
            this.tracks = [];
            this.currentTrackIndex = -1;
        }
    }
}