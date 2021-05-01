import { default as spieldoseAPI } from '../api.js';

const template = function () {
    return `
        <div class="tile is-ancestor" id="container_tiles">
            <div class="tile is-2 is-vertical" v-for="column in [0,1,2,3,4,5]">
                <div class="tile" v-for="row in [0,1,2,3,4,5]">
                    <img :src="getImageSource(covers[(5 * column) + row])" style="width:100%" @error="$event.target.src='/images/vinyl.png'">
                </div>
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
            if (img) {
                if (img.hash) {
                    return ('api/thumbnail?hash=' + img.hash);
                } else if (img.url) {
                    return ('api/thumbnail?url=' + img.url);
                } else {
                    return (null);
                }
            }
        },
        loadRandomAlbumImages: function () {
            spieldoseAPI.album.getRandomAlbumCovers(32, (response) => {
                if (response.status == 200) {
                    this.covers = response.data.covers.map((cover, idx) => { cover.id = idx; return (cover); });
                }
            });
        }
    }
}