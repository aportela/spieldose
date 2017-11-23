var container = (function () {
    "use strict";

    var template = function () {
        return `
        <div class="is-light is-bold">

            <nav class="navbar is-hidden-tablet" role="navigation" aria-label="main navigation">
                <div class="navbar-brand is-unselectable">
                    <a class="navbar-item has-text-weight-bold" href="https://github.com/aportela/spieldose">
                        spieldose
                    </a>
                    <button class="button navbar-burger" v-on:click.prevent="mobileMenu = !mobileMenu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
                <div class="navbar-menu is-active" v-show="mobileMenu">
                    <spieldose-menu-component></spieldose-menu-component>
                </div>
            </nav>

            <div class="hero is-fullheight is-light is-bold">
                <div class="columns is-gapless" id="main_container">
                    <aside id="aside-player" class="column is-3">
                        <spieldose-player-component v-bind:mobileMenu="mobileMenu"></spieldose-player-component>
                    </aside>
                    <section class="column is-9">
                        <keep-alive>
                            <router-view></router-view>
                        </keep-alive>
                    </section>
                </div>
            </div>

        </div>
    `;
    };

    var module = Vue.component('spieldose-app-component', {
        template: template(),
        data: function () {
            return ({
                mobileMenu: false,
                playerData: sharedPlayerData,
            });
        }, created: function () {
            var self = this;
            self.playerData.loadRandomTracks(initialState.defaultResultsPage, function () {
                self.playerData.play();
            });
        }
    });

    return (module);
})();