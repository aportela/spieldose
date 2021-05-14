const template = function () {
    return `
        <div v-if="url">
            <img class="album-thumbnail" :src="url" @error="unsetImage()">
        </div>
    `;
};

export default {
    name: 'spieldose-image-album',
    template: template(),
    data: function () {
        return ({
            url: null
        });
    },
    props: ['src'],
    created: function () {
        this.url = this.getAlbumImageUrl(this.src);
    },
    watch: {
        src: function(newValue, oldValue) {
            this.url = this.getAlbumImageUrl(newValue);
        }
    },
    methods: {
        getAlbumImageUrl: function (value) {
            if (value) {
                if (value.indexOf("http") == 0) {
                    return ("api/thumbnail?url=" + encodeURIComponent(value));
                } else {
                    return ("api/thumbnail?hash=" + value);
                }
            } else {
                /**
                 * Vinyl disc icon credits: Jordan Green (http://www.jordangreenphoto.com/)
                 * https://jordygreen.deviantart.com/art/Vinyl-Disc-Icon-Updated-57968239
                 */
                return ("images/image-album-not-set.png");
            }
        },
        unsetImage: function () {
            this.url = 'images/image-album-not-set.png';
        }
    }
}