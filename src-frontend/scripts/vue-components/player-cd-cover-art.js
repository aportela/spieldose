const template = function () {
    return `
        <div id="album-cover-container" class="album-cover-container-rotate" v-if="rotating" @click.prevent="rotating = false">
            <img id="album-cover-animated" :src="thumbnailSourceURL" v-if="thumbnailSourceURL != 'images/vinyl.png'">
        </div>
        <img id="album-cover" :src="thumbnailSourceURL" v-else @click.prevent="rotating = true">
    `;
};

export default {
    name: 'spieldose-player-cd-cover-art',
    template: template(),
    data: function () {
        return ({
            rotating: false,
            thumbnailSourceURL: 'images/vinyl.png',
            cacheImage: null,
            cacheImageURL: null
        });
    },
    props: [
        'url', 'rotate'
    ],
    watch: {
        url: function (newValue) {
            if (newValue) {
                this.cacheImage = new Image();
                this.cacheImage.onload = () => {
                    this.thumbnailSourceURL = this.cacheImageURL;
                }
                this.cacheImage.onerror = (e) => {
                    console.error(e);
                    this.thumbnailSourceURL = 'images/vinyl.png';
                }
                this.thumbnailSourceURL = 'images/vinyl.png';
                this.cacheImage.src = null;
                this.cacheImage.src = newValue;
                this.cacheImageURL = newValue;
            } else {
                this.thumbnailSourceURL = 'images/vinyl.png';
            }
        }
    },
    created: function () {
        if (this.rotate) {
            this.rotating = true;
        }
    }
}