import { default as imageAlbum } from './image-album.js';

const template = function () {
    return `
        <div id="artist-header-block">
            <div id="artist-header-block-background-image" v-if="false && artist && artist.image" :style="'background-image: url(api/thumbnail?url=' + artist.image + ')'"></div>
            <div id="artist-header-block-background-image" v-if="artist && artist.image" :style="'background-image: url(' + artist.image + ');'"></div>
            <div id="artist-header-block-background-overlay"></div>
            <div id="artist-header-block-content">
                <div class="p-6">
                    <p class="has-text-white title is-1">{{ artist.name }}</p>
                    <p class="has-text-white title is-6"><span class="has-text-grey"><i class="fas fa-users"></i> Listeners:</span> <span class="has-text-grey-lighter">{{ artist.totalListeners || 0 }} user/s</span></p>
                    <p class="has-text-white title is-6"><span class="has-text-grey"><i class="fas fa-compact-disc"></i> Total plays:</span> <span class="has-text-grey-lighter">{{ artist.playCount || 0 }} times</span></p>
                    <div class="columns">
                        <div class="column is-half" v-if="latestAlbum">
                            <figure class="image is-96x96 is-pulled-left">
                                <spieldose-image-album :src="latestAlbum.image"></spieldose-image-album>
                            </figure>
                            <p style="margin-left: 110px; margin-top: 10px;">
                                <span class="is-size-7 has-text-grey">LATEST RELEASE
                                <br><strong class="is-size-6 has-text-grey-lighter">{{ latestAlbum.name }}</strong>
                                <br><span class="is-size-6 has-text-grey-light" v-if="latestAlbum.year">{{ latestAlbum.year }}</span>
                            </p>
                        </div>
                        <div class="column is-half" v-if="popularAlbum">
                            <figure class="image is-96x96 is-pulled-left">
                                <spieldose-image-album :src="popularAlbum.image"></spieldose-image-album>
                            </figure>
                            <p style="margin-left: 110px; margin-top: 4px;">
                                <span class="is-size-7 has-text-grey">POPULAR
                                <br><strong class="is-size-6 has-text-grey-lighter">{{ popularAlbum.name }}</strong>
                                <br><span class="is-size-6 has-text-grey-light" v-if="popularAlbum.year">{{ popularAlbum.year }}</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div id="bottom">
                    <div class="tabs is-centered is-small">
                        <ul>
                            <li :class="{ 'is-active': currentTab == 'overview' }"><router-link :class="'has-text-grey-lighter'" :to="{ name: 'artistOverview', params: $route.params }">Overview</router-link></li>
                            <li :class="{ 'is-active': currentTab == 'biography' }"><router-link :class="'has-text-grey-lighter'" :to="{ name: 'artistBiography', params: $route.params }">Biography</router-link></li>
                            <li :class="{ 'is-active': currentTab == 'similarArtists' }"><router-link :class="'has-text-grey-lighter'" :to="{ name: 'artistSimilarArtists', params: $route.params }">Similar artists</router-link></li>
                            <li :class="{ 'is-active': currentTab == 'albums' }"><router-link :class="'has-text-grey-lighter'" :to="{ name: 'artistAlbums', params: $route.params }">Albums</router-link></li>
                            <li :class="{ 'is-active': currentTab == 'tracks' }"><router-link :class="'has-text-grey-lighter'" :to="{ name: 'artistTracks', params: $route.params }">Tracks</router-link></li>
                            <li :class="{ 'is-active': currentTab == 'stats' }"><router-link :class="'has-text-grey-lighter'" :to="{ name: 'artistStats', params: $route.params }">Stats</router-link></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    `;
};


export default {
    name: 'spieldose-browse-artist-header',
    template: template(),
    props: [
        'artist',
    ],
    computed: {
        currentTab: function () {
            let tab = null;
            switch (this.$route.name) {
                case 'artistBiography':
                    tab = 'biography';
                    break;
                case 'artistSimilarArtists':
                    tab = 'similarArtists';
                    break;
                case 'artistAlbums':
                    tab = 'albums';
                    break;
                case 'artistTracks':
                    tab = 'tracks';
                    break;
                case 'artistStats':
                    tab = 'stats'
                    break;
                default:
                    tab = 'overview';
                    break;
            }
            return (tab);
        },
        latestAlbum: function () {
            // TODO: check order
            if (this.artist && this.artist.albums) {
                return (this.artist.albums[this.artist.albums.length - 1]);
            } else {
                return (null);
            }
        },
        popularAlbum: function () {
            // TODO
            if (this.artist && this.artist.albums) {
                return (this.artist.albums[0]);
            } else {
                return (null);
            }
        }
    },
    components: {
        'spieldose-image-album': imageAlbum
    }
}