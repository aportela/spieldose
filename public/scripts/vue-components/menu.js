"use strict";

var vTemplateMenu = function () {
    return `
    <aside class="menu">
        <hr class="dropdown-divider">
        <ul class="menu-list">
            <li><a v-bind:class="{ 'is-active': actualRouteName == 'search' }" v-on:click.prevent="$router.push({ name: 'search' })"><i class="fa fa-search"></i> Search</a></li>
            <li><a v-bind:class="{ 'is-active': actualRouteName == 'dashboard' }" v-on:click.prevent="$router.push({ name: 'dashboard' })"><i class="fa fa-home"></i> dashboard</a></li>
            <li><a v-bind:class="{ 'is-active': actualRouteName == 'playlists' }" v-on:click.prevent="$router.push({ name: 'playlists' })"><i class="fa fa-list-alt"></i> playlists</a></li>
            <li><a v-bind:class="{ 'is-active': actualRouteName == 'artist' || actualRouteName == 'artists' }" v-on:click.prevent="$router.push({ name: 'artists' })"><i class="fa fa-user"></i> browse artists</a></li>
            <li><a v-bind:class="{ 'is-active': actualRouteName == 'albums' }" v-on:click.prevent="$router.push({ name: 'albums' })"><i class="fa fa-file-audio-o"></i> browse albums</a></li>
            <li><a v-on:click.prevent="signout();"><i class="fa fa-sign-out"></i> signout</a></li>
        </ul>
    </aside>
    `;
}

/* app (logged) menu component */
var menu = Vue.component('spieldose-menu-component', {
    template: vTemplateMenu(),
    data: function () {
        return ({
            actualRouteName: null
        });
    },
    watch: {
        '$route'(to, from) {
            this.actualRouteName = to.name;
        }
    },
    created: function() {
        this.actualRouteName = this.$route.name;
    },
    methods: {
        signout: function (e) {
            bus.$emit("signOut");
        }, changeSection(routeName) {
            this.$router.push({ name: routeName });
        }
    }
});
