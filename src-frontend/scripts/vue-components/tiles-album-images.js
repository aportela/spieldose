import { default as spieldoseAPI } from '../api.js';

const template = function () {
    return `
        <div class="tile is-ancestor" id="container_tiles">
            <div class="tile is-2 is-vertical" v-for="column in [0,1,2,3,4,5]">
                <div class="tile" v-for="row, idx in [0,1,2,3,4,5]" :key="idx">
                    <img :src="getImageSource(covers[(5 * column) + row])" style="width:100%" class="blur" @error="onImageError($event)">
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
                    return ('api2/thumbnail/400/400/' + img.hash);
                } else if (img.url) {
                    return ('api2/thumbnail?url=' + img.url);
                } else {
                    return (null);
                }
            }
        },
        loadRandomAlbumImages: function () {
            spieldoseAPI.album.getRandomAlbumCovers(32, 400, 400).then(response => {
                this.covers = response.data.covers.map((cover, idx) => { return ({ id: idx, hash: cover }); });
                console.log(this.covers);
            }).catch(error => {
            });
        },
        onImageError: function (event) {
            event.target.src = '/images/vinyl.png';
        }
    }
}