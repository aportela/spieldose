const template = function () {
    return `
        <div class="tile is-ancestor" id="container_tiles">
            <div class="tile is-2 is-vertical" v-for="column in [0,1,2,3,4,5]">
                <div class="tile" v-for="row in [0,1,2,3,4,5]">
                    <img v-if="imageURLs.length > 0" :src="getImgSource((5 * column) + row)" style="width: 100%" class="blur" @error="onImageError($event)">
                    <img src="/images/vinyl.png" v-else>
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
            imageURLs: []
        })
    },
    created: function () {
        this.loadRandomAlbumImages();
    },
    mounted: function () {
    },
    methods: {
        getImgSource: function (index) {
            if (index < this.imageURLs.length) {
                return (this.imageURLs[index]);
            } else {
                return ('/images/vinyl.png');
            }
        },
        loadRandomAlbumImages: function () {
            this.$api.album.getRandomAlbumCoverThumbnails().then(response => {
                this.imageURLs = response.data.coverURLs;
            }).catch(error => {
            });
        },
        onImageError: function (event) {
            event.target.src = '/images/vinyl.png';
        }
    }
}