import { default as player } from '../vue-components/player.js';
import { default as menu } from '../vue-components/menu.js';

const template = function () {
    return `
        <div>
            <section class="section is-fullheight">
                <div class="columns is-desktop is-centered">
                    <!-- TODO: test class "is-hidden-touch" for mobile -->
                    <div class="is-narrow column" id="sidebar">
                        <spieldose-component-player :track="currentTrack" @previous="onPreviousTrack" @next="onNextTrack" :showNavigationMenu="showNavigationMenu" @toggle-navigation-menu="onToggleNavigationMenu" :showSectionDetails="showSectionDetails" @toggle-section-details="onToggleSectionDetails">
                            <template slot="top-left-icon">
                                <a class="list__link" href="#" @click.prevent="loadTracks('')"><i class="fa fa-navicon"></i></a>
                            </template>
                            <template slot="top-right-icon">
                                <a class="list__link" href="#" @click.prevent><i class="fa fa-search"></i></a>
                            </template>
                        </spieldose-component-player>
                        <spieldose-menu-component v-if="showNavigationMenu"></spieldose-menu-component>
                    </div>
                    <div class="column" v-show="showSectionDetails">
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
            currentTrack: {},
            showNavigationMenu: true,
            showSectionDetails: true
        });
    },
    components: {
        'spieldose-component-player': player,
        'spieldose-menu-component': menu
    },
    created: function () {
        this.$bus.on('onTrackChanged', (data) => { this.currentTrack = data.track; });
    },
    methods: {
        onPreviousTrack: function () {

        },
        onNextTrack: function () {

        },
        onToggleNavigationMenu: function () {
            this.showNavigationMenu = !this.showNavigationMenu;
        },
        onToggleSectionDetails: function () {
            this.showSectionDetails = !this.showSectionDetails;
        }
    }
}