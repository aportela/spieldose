import { default as playerComponent } from './player.js';
import { default as menu } from './menu.js';
import { default as playerNavBar } from './player-navbar.js';

const template = function () {
    return `
        <div>
            <section class="section is-fullheight">
                <div class="columns is-desktop">
                    <!-- TODO: test class "is-hidden-touch" for mobile -->
                    <div class="is-narrow column">
                        <spieldose-player :track="{}"></spieldose-player>
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
    name: 'spieldose-app-component',
    template: template(),
    components: {
        'spieldose-player': playerComponent,
        'spieldose-menu-component': menu,
        'player-navbar': playerNavBar
    },
    created: function () {
        console.log(this.$api);
    }
}