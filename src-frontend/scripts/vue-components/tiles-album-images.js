const template = function () {
    return `
        <div class="tile is-ancestor mt-0" id="container_tiles">
            <div class="tile is-2 is-vertical" v-for="column in [0,1,2,3,4,5]">
                <div class="tile" v-for="row in [0,1,2,3,4,5]" :style="'background-color: ' + getRandomColor() + ';'">
                    <img v-if="imageURLs.length > 0" :src="getImgSource((5 * column) + row)" @error="onImageError($event)">
                    <img :src="defaultImage" v-else>
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
            defaultImage: 'images/vinyl.png',
            imageURLs: []
        })
    },
    created: function () {
        this.loadRandomAlbumImages();
    },
    methods: {
        // https://stackoverflow.com/q/10014271
        getRandomColor: function () {
            const allowed = "ABCDEF0123456789";
            let S = "#";
            while (S.length < 7) {
                S += allowed.charAt(Math.floor((Math.random() * 16) + 1));
            }
            return (S);
        },
        getImgSource: function (index) {
            if (index < this.imageURLs.length) {
                return (this.imageURLs[index]);
            } else {
                return (this.defaultImage);
            }
        },
        loadRandomAlbumImages: function () {
            this.$api.album.getRandomAlbumCoverThumbnails().then(response => {
                if (response.data.coverURLs.length >= 32) {
                    this.imageURLs = Array.isArray(response.data.coverURLs) ? response.data.coverURLs : [];
                }
            }).catch(error => {
                this.imageURLs = [];
            });
        },
        onImageError: function (event) {
            event.target.src = this.defaultImage;
        }
    }
}