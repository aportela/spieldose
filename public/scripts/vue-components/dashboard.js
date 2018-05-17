let dashboard = (function () {
    "use strict";

    const template = function () {
        return `
            <!-- dashboard template inspired by daniel (https://github.com/dansup) -->
            <div class="container is-fluid box is-marginless is-unselectable">
                <p class="title is-1 has-text-centered">Dashboard</p>
                <div class="columns is-mobile is-multiline">
                    <div class="column is-one-third-desktop is-full-mobile">
                        <spieldose-dashboard-toplist v-bind:type="'topTracks'" v-bind:title="$t('dashboard.labels.topPlayedTracks')" v-bind:listItemCount="5" v-bind:showPlayCount="false"></spieldose-dashboard-toplist>
                    </div>
                    <div class="column is-one-third-desktop is-full-mobile">
                        <spieldose-dashboard-toplist v-bind:type="'topArtists'" v-bind:title="$t('dashboard.labels.topPlayedArtists')" v-bind:listItemCount="5" v-bind:showPlayCount="false"></spieldose-dashboard-toplist>
                    </div>
                    <div class="column is-one-third-desktop is-full-mobile">
                        <spieldose-dashboard-toplist v-bind:type="'topGenres'" v-bind:title="$t('dashboard.labels.topPlayedGenres')" v-bind:listItemCount="5" v-bind:showPlayCount="false"></spieldose-dashboard-toplist>
                    </div>
                </div>
                <div class="columns is-mobile is-multiline">
                    <div class="column is-one-third-desktop is-full-mobile">
                        <spieldose-dashboard-recent v-bind:type="'recentlyAdded'" v-bind:title="$t('dashboard.labels.recentlyAdded')" v-bind:listItemCount="5"></spieldose-dashboard-recent>
                    </div>
                    <div class="column is-one-third-desktop is-full-mobile">
                        <spieldose-dashboard-recent v-bind:type="'recentlyPlayed'" v-bind:title="$t('dashboard.labels.recentlyPlayed')" v-bind:listItemCount="5"></spieldose-dashboard-recent>
                    </div>
                </div>
                <div class="columns is-mobile is-multiline">
                    <div class="column is-full-mobile">
                        <spieldose-dashboard-play-stats></spieldose-dashboard-play-stats>
                    </div>
                </div>
            </div>
        `;
    };

    let module = Vue.component('spieldose-dashboard', {
        template: template()
    });

    return (module);
})();