let menu = (function () {
    "use strict";

    const template = function () {
        return `
            <nav id="menu" class="panel is-unselectable">
                <p class="panel-heading">{{ $t("menu.labels.header") }}</p>
                <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('dashboard') }" v-on:click.prevent="changeSection('dashboard');">
                    <span class="panel-icon"><i class="fas fa-chart-line"></i></span>
                    <span>{{ $t("menu.labels.dashboard") }}</span>
                </a>
                <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('nowPlaying') }" v-on:click.prevent="changeSection('nowPlaying');">
                    <span class="panel-icon"><i class="fas fa-headphones"></i></span>
                    <span>{{ $t("menu.labels.currentPlaylist") }}</span>
                </a>
                <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('search') }" v-on:click.prevent="changeSection('search');">
                    <span class="panel-icon"><i class="fas fa-search"></i></span>
                    <span>{{ $t("menu.labels.search") }}</span>
                </a>
                <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('artists') }" v-on:click.prevent="changeSection('artists');">
                    <span class="panel-icon"><i class="fas fa-user"></i></span>
                    <span>{{ $t("menu.labels.browseArtists") }}</span>
                </a>
                <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('albums') }" v-on:click.prevent="changeSection('albums');">
                    <span class="panel-icon"><i class="fas fa-circle"></i></span>
                    <span>{{ $t("menu.labels.browseAlbums") }}</span>
                </a>
                <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('paths') }" v-on:click.prevent="changeSection('paths');">
                    <span class="panel-icon"><i class="fas fa-folder-open"></i></span>
                    <span>{{ $t("menu.labels.browsePaths") }}</span>
                </a>
                <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('playlists') }" v-on:click.prevent="changeSection('playlists');">
                    <span class="panel-icon"><i class="fas fa-list-alt"></i></span>
                    <span>{{ $t("menu.labels.browsePlaylists") }}</span>
                </a>
                <a class="panel-block" v-on:click.prevent="signout();">
                    <span class="panel-icon"><i class="fas fa-sign-out-alt"></i></span>
                    <span>{{ $t("menu.labels.signOut") }}</span>
                </a>
            </nav>
        `;
    };

    /* section menu component */
    let module = Vue.component('spieldose-menu-component', {
        template: template(),
        mixins: [mixinNavigation, mixinSession]
    });

    return (module);
})();