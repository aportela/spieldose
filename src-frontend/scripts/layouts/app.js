import { default as playerComponent } from '../vue-components/player.js';
import { default as menu } from '../vue-components/menu.js';
import { default as playerNavBar } from '../vue-components/player-navbar.js';

const template = function () {
    return `
        <div>
            <section class="section is-fullheight">
                <div class="columns is-desktop">
                    <!-- TODO: test class "is-hidden-touch" for mobile -->
                    <div class="is-narrow column">
                        <spieldose-player :track="track"></spieldose-player>
                        <spieldose-menu-component></spieldose-menu-component>
                    </div>
                    <div class="column">
                        <router-view></router-view>
                    </div>
                </div>
            </section>
            <player-navbar></player-navbar>
        </div>
    `;
};

export default {
    name: 'spieldose-layout-app',
    template: template(),
    data: function () {
        return ({
            track: {}
        });
    },
    components: {
        'spieldose-player': playerComponent,
        'spieldose-menu-component': menu,
        //'player-navbar': playerNavBar
    },
    created: function () {
        this.$bus.on('onTrackChanged', (data) => { this.track = data.track;});
    }
}