const template = function () {
    return `
        <div v-if="url">
            <img :src="url" :class="extraClass" @error="unsetImage()">
        </div>
    `;
};

export default {
    name: 'spieldose-image-radio-station',
    template: template(),
    data: function () {
        return ({
            url: null
        });
    },
    props: ['src', 'extraClass'],
    created: function () {
        this.url = this.getArtistImageUrl(this.src);
    },
    methods: {
        getArtistImageUrl: function (value) {
            if (value) {
                return ("api/thumbnail?url=" + value);
            } else {
                /**
                 * Music band icon credits: adiante apps (http://www.adianteapps.com/)
                 * https://www.iconfinder.com/icons/339940/band_festival_music_rock_stage_icon
                 */
                return ('images/image-radio-station-not-set.png');
            }
        },
        unsetImage: function () {
            this.url = 'images/image-radio-station-not-set.png';
        }
    }
}