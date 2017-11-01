"use strict";

var vTemplateMenu = function () {
    return `
    <ul id="menu">
        <li><a v-bind:class="{ 'active': section == '#/dashboard' }" href="#/dashboard"><div><i class="fa fa-2x fa-home"></i>dashboard</div></a></li>
        <li><a v-bind:class="{ 'active': section == '#/artists' }" href="#/artists"><div><i class="fa fa-2x fa-user"></i>browse artists</div></a></li>
        <li><a v-bind:class="{ 'active': section == '#/albums' }" href="#/albums"><div><i class="fa fa-2x fa-file-audio-o"></i>browse albums</div></a></li>
        <!--
        <li><a v-bind:class="{ 'active': section == '#/genres' }" href="#/genres"><div><i class="fa fa-2x fa-tags"></i>browse genres</div></a></li>
        <li><a v-bind:class="{ 'active': section == '#/preferences' }" href="#/preferences"><div><i class="fa fa-2x fa-cog"></i>preferences</div></a></li>
        -->
        <li><a href="/api/user/signout" v-on:click.prevent="signout"><div><i class="fa fa-2x fa-sign-out"></i>signout</div></a></li>
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
        bus.$on("hashChanged", function (hash) {
            self.section = hash;
            self.changeSection(self.section);
        });
        bus.$on("activateSection", function (s) {
            self.section = s;
        });
        self.changeSection(self.section);
    },
    methods: {
        signout: function (e) {
            bus.$emit("signOut");
        }, changeSection(s) {
            bus.$emit("loadSection", s);
        }
    }
});
