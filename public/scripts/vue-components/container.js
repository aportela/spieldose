"use strict";

var vTemplateContainer = function () {
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
}

var container = Vue.component('spieldose-app-component', {
    template: vTemplateContainer(),
    data: function () {
        return ({
            playerData: sharedPlayerData,
        });
    }, created: function () {
        var self = this;
        self.playerData.loadRandomTracks(DEFAULT_SECTION_RESULTS_PAGE, function () {
            self.playerData.play();
        });
    }
});
