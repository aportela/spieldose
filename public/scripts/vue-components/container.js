var container = (function () {
    "use strict";

    var template = function () {
        return `
            <section class="hero is-fullheight">
                <div class="hero-body is-paddingless">
                    <div class="container is-fullwidth is-vcentered">
                        <div class="tabs is-centered is-medium is-toggle">
                            <ul>
                                <li v-bind:class="{ 'is-active': $route.name == 'player'}">
                                    <a v-on:click.prevent="$router.push({ name: 'player' })">
                                        <span class="icon is-small"><i class="fa fa-headphones"></i></span>
                                        <span>Now playing</span>
                                    </a>
                                </li>
                                <li v-bind:class="{ 'is-active': $route.name == 'search'}">
                                    <a v-on:click.prevent="$router.push({ name: 'search' })">
                                        <span class="icon is-small"><i class="fa fa-search"></i></span>
                                        <span>Search</span>
                                    </a>
                                </li>
                                <li v-bind:class="{ 'is-active': $route.name == 'artists'}">
                                    <a v-on:click.prevent="$router.push({ name: 'artists' })">
                                        <span class="icon is-small"><i class="fa fa-user"></i></span>
                                        <span>Browse artists</span>
                                    </a>
                                </li>
                                <li v-bind:class="{ 'is-active': $route.name == 'albums'}">
                                    <a v-on:click.prevent="$router.push({ name: 'albums' })">
                                        <span class="icon is-small"><i class="fa fa-circle"></i></span>
                                        <span>Browse albums</span>
                                    </a>
                                </li>
                                <li v-bind:class="{ 'is-active': $route.name == 'playlists'}">
                                    <a v-on:click.prevent="$router.push({ name: 'playlists' })">
                                        <span class="icon is-small"><i class="fa fa-folder-open"></i></span>
                                        <span>Browse paths</span>
                                    </a>
                                </li>
                                <li v-bind:class="{ 'is-active': $route.name == 'paths'}">
                                    <a v-on:click.prevent="$router.push({ name: 'paths' })">
                                        <span class="icon is-small"><i class="fa fa-list-alt"></i></span>
                                        <span>Browse paths</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <keep-alive>
                            <router-view></router-view>
                        </keep-alive>
                    </div>
                </div>
            </section>
    `;
    };

    var module = Vue.component('spieldose-app-component', {
        template: template(),
        data: function () {
            return ({
                /*
                mobileMenu: false,
                playerData: sharedPlayerData,
                */
            });
        }, created: function () {
            /*
            var self = this;
            self.playerData.loadRandomTracks(initialState.defaultResultsPage, function () {
                self.playerData.play();
            });
            */
        }
    });

    return (module);
})();