import bus from "../plugins/bus";

const template = function () {
    return `
        <div class="field has-addons">
            <div class="control has-icons-left is-expanded is-small" :class="{ 'is-loading': loading}">
                <input class="input is-small" type="text" placeholder="Search" v-model.trim="searchQuery"
                    v-on:keyup.enter="onSearch">
                <span class="icon is-small is-left">
                    <i class="fas fa-search"></i>
                </span>
                <span class="icon is-small is-right">
                    <i class="fas fa-loading"></i>
                </span>
            </div>
            <div class="control">
                <a class="button is-small" style="background: #d30320; color: #fafafa;"
                    @click.prevent="onSearch" :disabled="loading">
                    Search
                </a>
            </div>
        </div>
        <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
            <thead>
                <tr>
                    <th class="has-text-right">Index</th>
                    <th>Artist</th>
                    <th>Album Artist</th>
                    <th>Album</th>
                    <th class="has-text-right">Track nº</th>
                    <th>Title</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="track,index in tracks" :key="index"
                    :class="{ 'is-selected': currentTrack.id == track.id } ">
                    <td class="has-text-right is-clickable" @click.prevent="currentTrackIndex = index;"> <i
                            class="fa-solid fa-play mr-2" v-if="currentTrack.id == track.id"></i>{{ index + 1 }}/{{ tracks.length }}
                    </td>
                    <td class="is-clickable" @click.prevent="loadTracks('', track.artist, '', '');">{{
                        track.artist
                        }}</td>
                    <td class="is-clickable" @click.prevent="loadTracks('', '', track.albumArtist, '');">{{
                        track.albumArtist
                        }}</td>
                    <td class="is-clickable"
                        @click.prevent="loadTracks('', '', track.albumArtist, track.album);">{{
                        track.album }}</td>
                    <td class="has-text-right">{{ track.trackNumber }}</td>
                    <td class="is-clickable" @click.prevent="currentTrackIndex = index;">{{ track.title }}</td>
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
            tracks: [],
            currentTrackIndex: -1,
            searchQuery: null,
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
           this.$bus.emit('onTrackChanged', { track: this.currentTrack });
        }
    },
    created: function () {
        this.loadTracks();
    },
    methods: {
        loadTracks: function (query, artist, albumArtist, album) {
            if (artist || album) {
                this.searchQuery = null;
            }
            this.loading = true;
            this.$api.track.search(this.searchQuery, artist, albumArtist, album).then(success => {
                this.tracks = success.data.tracks;
                this.currentTrackIndex = 0;
            }).catch(error => {
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
                        this.setAPIError(error.getApiErrorData());
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
    }
}