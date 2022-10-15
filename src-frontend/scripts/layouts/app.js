import { default as player } from '../vue-components/player.js';
import { default as menu } from '../vue-components/menu.js';
import { default as playerNavBar } from '../vue-components/player-navbar.js';

const template = function () {
    return `
        <div>
            <section class="section is-fullheight">
                <div class="columns is-desktop">
                    <!-- TODO: test class "is-hidden-touch" for mobile -->
                    <div class="is-narrow column" id="sidebar">
                        <spieldose-component-player :track="currentTrack" @previous="onPreviousTrack" @next="onNextTrack">
                            <template slot="top-left-icon">
                                <a class="list__link" href="#" @click.prevent="loadTracks('')"><i class="fa fa-navicon"></i></a>
                            </template>
                            <template slot="top-right-icon">
                                <a class="list__link" href="#" @click.prevent><i class="fa fa-search"></i></a>
                            </template>
                        </spieldose-component-player>
                        <spieldose-menu-component></spieldose-menu-component>
                    </div>
                    <div class="column">
                        <router-view></router-view>
                    </div>
                </div>
            </section>
            <!--
            <player-navbar></player-navbar>
            -->
        </div>
    `;
};

export default {
    name: 'spieldose-layout-app',
    template: template(),
    data: function () {
        return ({
            currentTrack: {}
        });
    },
    components: {
        'spieldose-component-player': player,
        'spieldose-menu-component': menu
        //'player-navbar': playerNavBar
    },
    created: function () {
        this.$bus.on('onTrackChanged', (data) => { this.currentTrack = data.track; });
    },
    methods: {
        onPreviousTrack: function () {

        },
        onNextTrack: function () {

        }
    }
}