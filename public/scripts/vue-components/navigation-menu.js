"use strict";

var vTemplateNavigationMenu = function () {
    return `
    <nav class="navbar is-dark" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <div class="navbar-item">
                <span class="icon"><i class="fa fa-music rainbow-transition" aria-hidden="true"></i></span> spieldose
            </div>
        </div>
        <div class="navbar-menu">
            <div class="navbar-start">
                <a class="navbar-item" v-bind:class="{ 'is-active': actualRouteName == 'dashboard' }" v-on:click.prevent="changeSection('dashboard')"><span class="icon is-small"><i class="fa fa-home"></i></span> dashboard</a>
                <a class="navbar-item" v-bind:class="{ 'is-active': actualRouteName == 'nowplaying' }" v-on:click.prevent="changeSection('nowplaying')"><span class="icon is-small"><i class="fa fa-headphones"></i></span> now playing</a>
                <a class="navbar-item" v-bind:class="{ 'is-active': actualRouteName == 'artist' || actualRouteName == 'artists' }" v-on:click.prevent="changeSection('artists')"><span class="icon is-small"><i class="fa fa-user"></i></span> browse artists</a>
                <a class="navbar-item" v-bind:class="{ 'is-active': actualRouteName == 'albums' }"  v-on:click.prevent="changeSection('albums')"><span class="icon is-small"><i class="fa fa-file-audio-o"></i></span> browse albums</a>
                <div class="navbar-item is-mega">
                    <spieldose-search></spieldose-search>
                </div>
            </div>
            <div class="navbar-end">
                <a class="navbar-item" v-on:click.prevent="signout()"><span class="icon is-small"><i class="fa fa-sign-out"></i></span> signout</a>
            </div>
        </div>
        <button class="button navbar-burger">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </nav>
    `;
}

/* app (logged) menu component */
var navigationMenu = Vue.component('spieldose-navigation-menu-component', {
    template: vTemplateNavigationMenu(),
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
    created: function () {
        this.actualRouteName = this.$route.name;
    },
    methods: {
        signout: function (e) {
            bus.$emit("signOut");
        }, changeSection(routeName) {
            bus.$emit("changeRouterPath", routeName);
        }
    }
});
