var menu = (function () {
    "use strict";

    var template = function () {
        return `
        <nav class="panel is-unselectable">
            <p class="panel-heading">
            Menu
            </p>
            <a v-on:click.prevent="$router.push({ name: 'dashboard' })" class="panel-block" v-bind:class="{ 'is-active': $route.name == 'dashboard'}">
            <span class="panel-icon"><i class="fas fa-chart-line"></i></span>
            dashboard
            </a>
            <a v-on:click.prevent="$router.push({ name: 'nowPlaying' })" class="panel-block" v-bind:class="{ 'is-active': $route.name == 'nowPlaying'}">
            <span class="panel-icon"><i class="fas fa-headphones"></i></span>
            current playlist
            </a>
            <a v-on:click.prevent="$router.push({ name: 'search' })" class="panel-block" v-bind:class="{ 'is-active': $route.name == 'search'}">
            <span class="panel-icon"><i class="fas fa-search"></i></span>
            search
            </a>
            <a v-on:click.prevent="$router.push({ name: 'artists' })" class="panel-block" v-bind:class="{ 'is-active': $route.name == 'artists'}">
            <span class="panel-icon"><i class="fas fa-user"></i></span>
            browse artists
            </a>
            <a v-on:click.prevent="$router.push({ name: 'albums' })" class="panel-block" v-bind:class="{ 'is-active': $route.name == 'albums'}">
            <span class="panel-icon"><i class="fas fa-circle"></i></span>
            browse albums
            </a>
            <a v-on:click.prevent="$router.push({ name: 'paths' })" class="panel-block" v-bind:class="{ 'is-active': $route.name == 'paths'}">
            <span class="panel-icon"><i class="fas fa-folder-open"></i></span>
            browse paths
            </a>
            <a v-on:click.prevent="$router.push({ name: 'playlists' })" class="panel-block" v-bind:class="{ 'is-active': $route.name == 'playlists'}">
            <span class="panel-icon"><i class="fas fa-list-alt"></i></span>
            browse playlists
            </a>
            <a v-on:click.prevent="signout();" class="panel-block">
            <span class="panel-icon"><i class="fas fa-sign-out-alt"></i></span>
            signout
            </a>
        </nav>
        `;
    };

    /* app (logged) menu component */
    var module = Vue.component('spieldose-menu-component', {
        template: template(),
        data: function () {
            return ({
                playerData: sharedPlayerData,
                actualRouteName: null
            });
        },
        watch: {
            '$route'(to, from) {
                this.actualRouteName = to.name;
            }
        },
        created: function () {
            this.actualRouteName = this.$route.name;
        },
        methods: {
            signout: function (e) {
                this.playerData.dispose();
                bus.$emit("signOut");
            }, changeSection(routeName) {
                this.$router.push({ name: routeName });
            }
        }
    });

    return (module);
})();