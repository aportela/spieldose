import { mixinPlayer } from '../mixins.js';
import { default as imageAlbum } from './image-album.js';

const template = function () {
    return `
        <div class="browse-album-item">
            <a class="play-album" :title="$t('commonLabels.playThisAlbum')" @click.prevent="playAlbumTracks(album.name, album.artist, album.year);">
                <spieldose-image-album :src="album.image"></spieldose-image-album>
                <i class="fas fa-play fa-4x"></i>
                <img class="vinyl no-cover" src="images/vinyl.png" />
            </a>
            <div class="album-info">
                <p class="album-name">{{ album.name }}</p>
                <p v-if="album.artist" class="artist-name">{{ $t("commonLabels.by") }}
                    <router-link :title="$t('commonLabels.navigateToArtistPage')" :to="{ name: 'artist', params: { artist: album.artist }}">{{ album.artist }}</router-link>
                    <span v-show="album.year"> ({{ album.year }})</span>
                </p>
                <p v-else class="artist-name">{{ $t("commonLabels.by") }} {{ $t("browseAlbums.labels.unknownArtist") }} <span v-show="album.year"> ({{ album.year }})</span></p>
            </div>
        </div>
    `;
};

export default {
    name: 'spieldose-album',
    template: template(),
    data: function () {
        return ({
            url: null
        });
    },
    props: ['album'],
    mixins: [
        mixinPlayer
    ],
    components: {
        'spieldose-image-album': imageAlbum
    }
}