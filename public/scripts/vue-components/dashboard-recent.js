"use strict";

var vTemplateDashboardRecent = function () {
    return `
    <section class="panel chart">
        <p class="panel-heading"><span class="icon"><i v-if="loading" class="fa fa-cog fa-spin fa-fw"></i><i v-else-if="errors" class="fa fa-cog fa-exclamation-triangle"></i><i v-else class="fa fa-clock-o"></i></span> {{ title }}</p>
        <p class="panel-tabs">
            <a v-bind:class="{ 'is-active' : entity == 0 }" v-on:click.prevent="changeEntity(0)" href="#">Tracks</a>
            <a v-bind:class="{ 'is-active' : entity == 1 }" v-on:click.prevent="changeEntity(1)" href="#">Artists</a>
            <a v-bind:class="{ 'is-active' : entity == 2 }" v-on:click.prevent="changeEntity(2)" href="#">Albums</a>
        </p>
        <div class="panel-block cut-text">
            <ol v-if="items.length > 0">
                <li class="is-small" v-if="entity == 0" v-for="item, i in items"><i v-on:click="playTrack(item);" class="cursor-pointer fa fa-play" title="play this track"></i> <i v-on:click="enqueueTrack(item);" class="cursor-pointer fa fa-cog fa-plus-square" title="enqueue this track"></i> {{ item.title }}<span v-if="item.artist"> / <a v-bind:href="'/#/app/artist/' + $router.encodeSafeName(item.artist)">{{ item.artist }}</a></span></li>
                <li class="is-small" v-if="entity == 1" v-for="item, i in items"><a v-bind:href="'/#/app/artist/' + $router.encodeSafeName(item.artist)">{{ item.artist }}</a></li>
                <li class="is-small" v-if="entity == 2" v-for="item, i in items"><i v-on:click="playAlbum(item);" class="cursor-pointer fa fa-play" title="play this album"></i> <i v-on:click="enqueueAlbum(item);" class="cursor-pointer fa fa-cog fa-plus-square" title="enqueue this album"></i> {{ item.album }}<span v-if="item.artist"> / <a v-bind:href="'/#/app/artist/' + $router.encodeSafeName(item.artist)">{{ item.artist }}</a></span></li>
            </ol>
            <p v-else-if="items.length == 0 && ! loading && ! errors">not enough data for the stats</p>
            <p v-else-if="errors">error loading data (invalid response from server)</p>
        </div>
    </section>
    `;
}

/* app chart (test) component */
var dashboardRecent = Vue.component('spieldose-dashboard-recent', {
    template: vTemplateDashboardRecent(),
    data: function () {
        return ({
            loading: false,
            errors: false,
            entity: 0,
            items: [],
            playerData: sharedPlayerData,
        });
    },
    created: function () {
        this.loadChartData();
    }, methods: {
        loadChartData: function () {
            var self = this;
            self.loading = true;
            self.errors = false;
            self.items = [];
            var url = null;
            if (this.type == "recentlyAdded") {
                url = '/api/metrics/recently_added';
            } else if (this.type == "recentlyPlayed") {
                url = '/api/metrics/recently_played';
            }
            var d = {
                count: self.listItemCount
            };
            switch (this.entity) {
                case 0: // tracks
                    d.entity = "tracks";
                    break;
                case 1: // artists
                    d.entity = "artists";
                    break;
                case 2: // albums
                    d.entity = "albums";
                    break;
            }
            if (url) {
                jsonHttpRequest("POST", url, d, function (httpStatusCode, response) {
                    self.loading = false;
                    if (httpStatusCode == 200) {
                        self.errors = false;
                        self.items = response.metrics;
                    } else {
                        self.errors = true;
                        self.items = [];
                    }
                });
            } else {
                self.loading = false;
                self.errors = true;
            }
        }, changeEntity: function (e) {
            this.entity = e;
            this.loadChartData();
        }, playTrack: function (track) {
            this.playerData.replace([ track ]);
        }, enqueueTrack: function (track) {
            this.playerData.enqueue([ track ]);
        }, playAlbum: function(album) {
        }, enqueueAlbum: function(album) {
        }
    },
    props: ['type', 'title', 'listItemCount']
});
