var container = (function () {
    "use strict";

    var template = function () {
        return `
        <div class="hero is-fullheight is-light is-bold">
            <div class="columns is-gapless" id="main_container">
                <aside id="aside-player" class="column is-3">
                    <spieldose-player-component></spieldose-player-component>
                </aside>
                <section class="column is-9">
                    <keep-alive>
                        <router-view></router-view>
                    </keep-alive>
                </section>
            </div>
        </div>
    `;
    };

    var module = Vue.component('spieldose-app-component', {
        template: template(),
        data: function () {
            return ({
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