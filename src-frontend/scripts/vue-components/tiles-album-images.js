import { default as spieldoseAPI } from '../api.js';

const template = function () {
    return `
        <div class="tile is-ancestor" id="container_tiles">
            <div class="tile is-2 is-vertical" v-for="column in [0,1,2,3,4,5]">
                <div class="tile" v-for="row in [0,1,2,3,4,5]">
                    <img v-if="coverHashes.length > 0" :src="getImgSource((5 * column) + row)" style="width:100%" class="blur" @error="onImageError($event)">
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
            coverHashes: []
        })
    },
    created: function () {
        this.loadRandomAlbumImages();
    },
    mounted: function () {
    },
    methods: {
        getImgSource: function(index) {
            if (index < this.coverHashes.length) {
                return('api2/thumbnail/400/400/' + this.coverHashes[index]);
            } else {
                return('/images/vinyl.png');
            }
        },
        loadRandomAlbumImages: function () {
            spieldoseAPI.album.getRandomAlbumCovers(32, 400, 400).then(response => {
                this.coverHashes = response.data.coverHashes;
            }).catch(error => {
            });
        },
        onImageError: function (event) {
            event.target.src = '/images/vinyl.png';
        }
    }
}