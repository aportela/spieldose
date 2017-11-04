"use strict";

var vTemplateMenu = function () {
    return `
    <ul id="menu">
        <li><a v-bind:class="{ 'active': section == '#/dashboard' }" v-on:click.prevent="changeSection('/app/dashboard')"><div><i class="fa fa-2x fa-home"></i>dashboard</div></a></li>
        <li><a v-bind:class="{ 'active': section == '#/artists' }" v-on:click.prevent="changeSection('/app/artists')"><div><i class="fa fa-2x fa-user"></i>browse artists</div></a></li>
        <li><a v-bind:class="{ 'active': section == '#/albums' }" v-on:click.prevent="changeSection('/app/albums')"><div><i class="fa fa-2x fa-file-audio-o"></i>browse albums</div></a></li>
        <!--
        <li><a v-bind:class="{ 'active': section == '#/genres' }" v-on:click.prevent="changeSection('/app/genres')"><div><i class="fa fa-2x fa-tags"></i>browse genres</div></a></li>
        <li><a v-bind:class="{ 'active': section == '#/preferences' }" v-on:click.prevent="changeSection('/app/preferences')"><div><i class="fa fa-2x fa-cog"></i>preferences</div></a></li>
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
            xhr: false,
            section: window.location.hash
        });
    },
    created: function () {
        var self = this;
    },
    methods: {
        signout: function (e) {
            bus.$emit("signOut");
        }, changeSection(path) {
            bus.$emit("changeRouterPath", path);
        }
    }
});
