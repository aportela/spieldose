"use strict";

var vTemplateMenu = function () {
    return `
    <ul id="menu">
        <li><a v-bind:class="{ 'active': actualRouteName == 'dashboard' }" v-on:click.prevent="changeSection('dashboard')"><div><i class="fa fa-2x fa-home"></i>dashboard</div></a></li>
        <li><a v-bind:class="{ 'active': actualRouteName == 'artist' || actualRouteName == 'artists' }" v-on:click.prevent="changeSection('artists')"><div><i class="fa fa-2x fa-user"></i>browse artists</div></a></li>
        <li><a v-bind:class="{ 'active': actualRouteName == 'albums' }" v-on:click.prevent="changeSection('albums')"><div><i class="fa fa-2x fa-file-audio-o"></i>browse albums</div></a></li>
        <!--
        <li><a v-bind:class="{ 'active': actualRouteName == 'genres' }" v-on:click.prevent="changeSection('genres')"><div><i class="fa fa-2x fa-tags"></i>browse genres</div></a></li>
        <li><a v-bind:class="{ 'active': actualRouteName == 'preferences' }" v-on:click.prevent="changeSection('preferences')"><div><i class="fa fa-2x fa-cog"></i>preferences</div></a></li>
        -->
        <li><a href="/api/user/signout" v-on:click.prevent="signout()"><div><i class="fa fa-2x fa-sign-out"></i>signout</div></a></li>
    </ul>
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
            bus.$emit("changeRouterPath", routeName);
        }
    }
});
