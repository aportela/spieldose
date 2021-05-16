import { default as album } from './album.js';

const template = function () {
    return `
            <div class="content">
                <div class="is-clearfix">
                    <spieldose-album v-for="album, i in artist.albums" :key="album.name+album.artist+album.year" :album="album"></spieldose-album>
                </div>
            </div>
    `;
};


export default {
    name: 'spieldose-browse-artist-albums',
    template: template(),
    props: [
        'artist'
    ],
    components: {
        'spieldose-album': album
    }
}