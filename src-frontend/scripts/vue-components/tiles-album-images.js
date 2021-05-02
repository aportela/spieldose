import { default as spieldoseAPI } from '../api.js';

const template = function () {
    return `
        <div class="tile is-ancestor" id="container_tiles" v-if="! loading">
            <div class="tile is-2 is-vertical" v-for="column in [0,1,2,3,4,5]">
                <div class="tile" v-for="row in [0,1,2,3,4,5]">
                    <img v-if="covers && covers.length > 0" :src="getImageSource(covers[(5 * column) + row])" style="width:100%" @error="$event.target.src='/images/vinyl.png'">
                        <div v-else style="width: 100%; height: auto;" :style="'background: ' + getRandomColor()"></div>
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
            loading: false,
            covers: []
        })
    },
    created: function () {
        this.loadRandomAlbumImages();
    },
    methods: {
        // https://stackoverflow.com/a/1484514
        getRandomColor: function() {
            var letters = '0123456789ABCDEF';
            var color = '#';
            for (var i = 0; i < 6; i++) {
              color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        },
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
            this.loading = true;
            spieldoseAPI.album.getRandomAlbumCovers(32, (response) => {
                if (response.status == 200) {
                    if (response.data.covers.length == 32) {
                        this.covers = response.data.covers.map((cover, idx) => { cover.id = idx; return (cover); });
                    }
                }
                this.loading = false;
            });
        }
    }
}