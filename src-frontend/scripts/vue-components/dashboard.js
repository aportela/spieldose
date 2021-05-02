import { default as dashboardTopList } from './dashboard-toplist.js';
import { default as dashboardRecent } from './dashboard-recent.js';
import { default as dashboardPlayStats } from './dashboard-play-stats.js';

const template = function () {
    return `
        <!-- dashboard template inspired by daniel (https://github.com/dansup) -->
        <div class="container is-fluid box is-marginless is-unselectable">
            <p class="title is-1 has-text-centered">{{ $t("menu.labels.dashboard") }}</p>
            <div class="columns is-mobile is-multiline">
                <div class="column is-one-third-desktop is-full-mobile">
                    <spieldose-dashboard-toplist v-bind:type="'topTracks'" v-bind:title="$t('dashboard.labels.topPlayedTracks')" v-bind:listItemCount="5" v-bind:showPlayCount="false"></spieldose-dashboard-toplist>
                </div>
                <div class="column is-one-third-desktop is-full-mobile">
                    <spieldose-dashboard-toplist v-bind:type="'topArtists'" v-bind:title="$t('dashboard.labels.topPlayedArtists')" v-bind:listItemCount="5" v-bind:showPlayCount="false"></spieldose-dashboard-toplist>
                </div>
                <div class="column is-one-third-desktop is-full-mobile">
                    <spieldose-dashboard-toplist v-bind:type="'topAlbums'" v-bind:title="$t('dashboard.labels.topPlayedAlbums')" v-bind:listItemCount="5" v-bind:showPlayCount="false"></spieldose-dashboard-toplist>
                </div>
            </div>
            <div class="columns is-mobile is-multiline">
                <div class="column is-one-third-desktop is-full-mobile">
                    <spieldose-dashboard-toplist v-bind:type="'topGenres'" v-bind:title="$t('dashboard.labels.topPlayedGenres')" v-bind:listItemCount="5" v-bind:showPlayCount="false"></spieldose-dashboard-toplist>
                </div>
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

export default {
    name: 'spieldose-dashboard',
    template: template(),
    components: {
    'spieldose-dashboard-toplist': dashboardTopList,
    'spieldose-dashboard-recent': dashboardRecent,
    'spieldose-dashboard-play-stats': dashboardPlayStats
    }
}