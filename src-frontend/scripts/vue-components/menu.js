import { mixinNavigation, mixinSession } from '../mixins.js';

const template = function () {
    return `
        <nav id="menu" class="panel is-unselectable">
            <p class="panel-heading">{{ $t("menu.labels.header") }}</p>
            <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('dashboard') }" @click.prevent="changeSection('dashboard');">
                <span class="panel-icon"><i class="fas fa-chart-line"></i></span>
                <span>{{ $t("menu.labels.dashboard") }}</span>
            </a>
            <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('nowPlaying') }" @click.prevent="changeSection('nowPlaying');">
                <span class="panel-icon"><i class="fas fa-headphones"></i></span>
                <span>{{ $t("menu.labels.currentPlaylist") }}</span>
            </a>
            <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('search') }" @click.prevent="changeSection('search');">
                <span class="panel-icon"><i class="fas fa-search"></i></span>
                <span>{{ $t("menu.labels.search") }}</span>
            </a>
            <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('artists') }" @click.prevent="changeSection('artists');">
                <span class="panel-icon"><i class="fas fa-user"></i></span>
                <span>{{ $t("menu.labels.browseArtists") }}</span>
            </a>
            <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('albums') }" @click.prevent="changeSection('albums');">
                <span class="panel-icon"><i class="fas fa-circle"></i></span>
                <span>{{ $t("menu.labels.browseAlbums") }}</span>
            </a>
            <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('paths') }" @click.prevent="changeSection('paths');">
                <span class="panel-icon"><i class="fas fa-folder-open"></i></span>
                <span>{{ $t("menu.labels.browsePaths") }}</span>
            </a>
            <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('playlists') }" @click.prevent="changeSection('playlists');">
                <span class="panel-icon"><i class="fas fa-list-alt"></i></span>
                <span>{{ $t("menu.labels.browsePlaylists") }}</span>
            </a>
            <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('radioStations') }" @click.prevent="changeSection('radioStations');">
                <span class="panel-icon"><i class="fas fa-broadcast-tower"></i></span>
                <span>{{ $t("menu.labels.browseRadioStations") }}</span>
            </a>
            <a class="panel-block" @click.prevent="signout();">
                <span class="panel-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span>{{ $t("menu.labels.signOut") }}</span>
            </a>
        </nav>
    `;
};

export default {
    name: 'spieldose-menu-component',
    template: template(),
    mixins: [mixinNavigation, mixinSession]
}