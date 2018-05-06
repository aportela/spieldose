let menu = (function () {
    "use strict";

    const template = function () {
        return `
            <nav id="menu" class="panel is-unselectable">
                <p class="panel-heading">Menu</p>
                <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('dashboard') }" v-on:click.prevent="changeSection('dashboard');">
                    <span class="panel-icon"><i class="fas fa-chart-line"></i></span>
                    <span>dashboard</span>
                </a>
                <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('nowPlaying') }" v-on:click.prevent="changeSection('nowPlaying');">
                    <span class="panel-icon"><i class="fas fa-headphones"></i></span>
                    <span>current playlist</span>
                </a>
                <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('search') }" v-on:click.prevent="changeSection('search');">
                    <span class="panel-icon"><i class="fas fa-search"></i></span>
                    <span>search</span>
                </a>
                <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('artists') }" v-on:click.prevent="changeSection('artists');">
                    <span class="panel-icon"><i class="fas fa-user"></i></span>
                    <span>browse artists</span>
                </a>
                <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('albums') }" v-on:click.prevent="changeSection('albums');">
                    <span class="panel-icon"><i class="fas fa-circle"></i></span>
                    <span>browse albums</span>
                </a>
                <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('paths') }" v-on:click.prevent="changeSection('paths');">
                    <span class="panel-icon"><i class="fas fa-folder-open"></i></span>
                    <span>browse paths</span>
                </a>
                <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('playlists') }" v-on:click.prevent="changeSection('playlists');">
                    <span class="panel-icon"><i class="fas fa-list-alt"></i></span>
                    <span>browse playlists</span>
                </a>
                <a class="panel-block" v-on:click.prevent="signout();">
                    <span class="panel-icon"><i class="fas fa-sign-out-alt"></i></span>
                    <span>signout</span>
                </a>
            </nav>
        `;
    };

    /* section menu component */
    let module = Vue.component('spieldose-menu-component', {
        template: template(),
        mixins: [mixinPlayer],
        methods: {
            signout: function () {
                bus.$emit("signOut");
            }, isSectionActive: function (section) {
                return (this.$route.name == section);
            }, changeSection(routeName) {
                this.$router.push({ name: routeName });
            }
        }
    });

    return (module);
})();