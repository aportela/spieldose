var container = (function () {
    "use strict";

    var template = function () {
        return `
        <div class="is-light is-bold">

            <section class="hero is-fullheight">
                <div class="hero-body is-paddingless">
                    <div class="container is-fullwidth is-vcentered">
                        <div class="tabs is-centered is-medium is-toggle">
                            <ul>
                                <li class="is-active">
                                    <a>
                                    <span class="icon is-small"><i class="fa fa-headphones"></i></span>
                                    <span>Now playing</span>
                                    </a>
                                </li>
                                <li>
                                    <a>
                                    <span class="icon is-small"><i class="fa fa-search"></i></span>
                                    <span>Search</span>
                                    </a>
                                </li>
                                <li>
                                    <a>
                                    <span class="icon is-small"><i class="fa fa-user"></i></span>
                                    <span>Browse artists</span>
                                    </a>
                                </li>
                                <li>
                                    <a>
                                    <span class="icon is-small"><i class="fa fa-circle"></i></span>
                                    <span>Browse albums</span>
                                    </a>
                                </li>
                                <li>
                                    <a>
                                    <span class="icon is-small"><i class="fa fa-folder-open"></i></span>
                                    <span>Browse paths</span>
                                    </a>
                                </li>
                                <li>
                                    <a>
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