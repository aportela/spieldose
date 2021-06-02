import { default as imageArtist } from './image-artist.js';

const template = function () {
    return `
        <div class="columns">
            <div class="column is-6 is-offset-3">
                <div id="similar">
                    <h5 class="title is-5 has-text-centered">Similar artists</h5>
                    <div class="has-text-grey has-text-centered mb-4" v-for="similar, idx in artist.similarArtists" :key="similar.name" v-show="idx < 3">
                        <router-link :title="$t('commonLabels.navigateToArtistPage')" :to="{ name: 'artist', params: { artist: similar.name }}" :class="'has-text-grey'">
                            <figure class="image is-128x128" style="margin: 0px auto;">
                                <spieldose-image-artist :src="similar.image" :extraClass="'is-rounded'"></spieldose-image-artist>
                            </figure>
                            <p class="has-text-centered">{{ similar.name }}</p>
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
`;
};


export default {
    name: 'spieldose-browse-artist-similar',
    template: template(),
    props: [
        'artist'
    ],
    components: {
        'spieldose-image-artist': imageArtist
    },
    created: function() {
        console.log(this.artist);
    }
}