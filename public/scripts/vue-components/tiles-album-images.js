import { default as spieldoseAPI } from '../api.js';

const template = function () {
    return `
        <div class="row_tiles">
            <div v-for="c in [0,1,2,3,4]" class="column_tiles" :key="c">
                <img v-for="image, idx in covers" :key="image.id" v-if="idx < 8" :src="getImageSource(covers[(8*c)+idx])"  style="width:100%" class="blur" @error="event.target.src = '/images/vinyl.png'">
            </div>
        </div>
    `;
};

export default {
    name: 'spieldose-tiles-album-images',
    template: template(),
    data: function () {
        return ({
            covers: []
        })
    },
    created: function () {
        this.loadRandomAlbumImages();
    },
    mounted: function () {
    },
    methods: {
        getImageSource: function (img) {
            if (img.hash) {
                return ('api/thumbnail?hash=' + img.hash);
            } else if (img.url) {
                return ('api/thumbnail?url=' + img.url);
            } else {
                return (null);
            }
        },
        loadRandomAlbumImages: function () {
            spieldoseAPI.album.getRandomAlbumCovers(200, (response) => {
                if (response.ok) {
                    this.covers = response.body.covers.map((cover, idx) => { cover.id = idx; return (cover); });
                }
            });
        }
    }
}