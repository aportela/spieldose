"use strict";

var vTemplateDashboardRecent = function () {
    return `
    <div class="column is-one-third-desktop is-full-mobile">
        <section class="panel chart">
            <p class="panel-heading"><span class="icon"><i v-if="xhr" class="fa fa-cog fa-spin fa-fw"></i><i v-else class="fa fa-clock-o"></i></span> {{ title }}</p>
            <p class="panel-tabs">
                <a v-bind:class="{ 'is-active' : entity == 0 }" v-on:click.prevent="changeEntity(0)" href="#">Tracks</a>
                <a v-bind:class="{ 'is-active' : entity == 1 }" v-on:click.prevent="changeEntity(1)" href="#">Artists</a>
                <a v-bind:class="{ 'is-active' : entity == 2 }" v-on:click.prevent="changeEntity(2)" href="#">Albums</a>
            </p>
            <div class="panel-block">
                <ol v-if="items.length > 0">
                    <li class="is-small" v-if="entity == 0" v-for="item, i in items">{{ item.title + (item.artist ? " / " + item.artist: "") }}</li>
                    <li class="is-small" v-if="entity == 1" v-for="item, i in items">{{ item.artist }}</li>
                    <li class="is-small" v-if="entity == 2" v-for="item, i in items">{{ item.album + (item.album ? " / " + item.artist: "") }}</li>
                </ol>
            </div>
        </section>
    </div>
    `;
}

/* app chart (test) component */
var dashboardRecent = Vue.component('spieldose-dashboard-recent', {
    template: vTemplateDashboardRecent(),
    data: function () {
        return ({
            xhr: false,
            iconClass: 'fa-pie-chart',
            items: [],
            entity: 0
        });
    },
    created: function () {
        this.loadChartData();
    }, methods: {
        loadChartData: function () {
            var self = this;
            self.items = [];
            var url = null;
            if (this.type == "recentlyAdded") {
                url = '/api/metrics/recently_added';
            } else if (this.type == "recentlyPlayed") {
                url = '/api/metrics/recently_played';
            }
            var d = {};
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
            self.xhr = true;
            jsonHttpRequest("POST", url, d, function (httpStatusCode, response) {
                self.xhr = false;
                self.items = response.metrics;
            });
        }, changeEntity: function (e) {
            this.entity = e;
            this.loadChartData();
        }
    },
    props: ['type', 'title']
});
