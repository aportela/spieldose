var dashboardToplist = (function () {
    "use strict";

    var template = function () {
        return `
    <section class="panel chart">
        <p class="panel-heading">
            <span class="icon"><i v-if="loading" class="fa fa-cog fa-spin fa-fw"></i><i v-else-if="errors" class="fa fa-cog fa-exclamation-triangle"></i><i v-else class="fa fa-list"></i></span> {{ title }}
            <a v-on:click.prevent="load();" title="refresh data" class="icon pull-right"><i class="fa fa-refresh fa-fw"></i></a>
        </p>
        <p class="panel-tabs">
            <a href="#" v-bind:class="{ 'is-active' : interval == 0 }" v-on:click.prevent="changeInterval(0)">All Time</a>
            <a href="#" v-bind:class="{ 'is-active' : interval == 1 }" v-on:click.prevent="changeInterval(1)">Past week</a>
            <a href="#" v-bind:class="{ 'is-active' : interval == 2 }" v-on:click.prevent="changeInterval(2)">Past month</a>
            <a href="#" v-bind:class="{ 'is-active' : interval == 3 }" v-on:click.prevent="changeInterval(3)">Past semester</a>
            <a href="#" v-bind:class="{ 'is-active' : interval == 4 }" v-on:click.prevent="changeInterval(4)">Past Year</a>
        </p>
        <div class="panel-block cut-text">
            <ol v-if="items.length > 0">
                <li class="is-small" v-if="type == 'topTracks'" v-for="item, i in items"><i v-on:click="playTrack(item);" class="cursor-pointer fa fa-play" title="play this track"></i> <i v-on:click="enqueueTrack(item);" class="cursor-pointer fa fa-cog fa-plus-square" title="enqueue this track"></i> {{ item.title }}<span v-if="item.artist"> / <a v-on:click.prevent="$router.push({ name: 'artist', params: { artist: item.artist } })">{{ item.artist }}</a></span><span v-if="showPlayCount == true"> ({{ item.total }} plays)</span></li>
                <li class="is-small" v-if="type == 'topArtists'" v-for="item, i in items"><a v-on:click.prevent="$router.push({ name: 'artist', params: { artist: item.artist } })">{{ item.artist }}</a><span v-if="showPlayCount == true"> ({{ item.total }} plays)</span></li>
                <li class="is-small" v-if="type == 'topGenres'" v-for="item, i in items">{{ item.genre }}<span v-if="showPlayCount == true"> ({{ item.total }} plays)</span></li>
            </ol>
            <p v-else-if="items.length == 0 && ! loading && ! errors">not enough data for the stats</p>
            <p v-else-if="errors">error loading data (invalid response from server)</p>
        </div>
    </section>
    `;
    };

    /* app chart (test) component */
    var module = Vue.component('spieldose-dashboard-toplist', {
        template: template(),
        data: function () {
            return ({
                loading: false,
                errors: false,
                interval: 0,
                items: [],
                playerData: sharedPlayerData,
            });
        },
        created: function () {
            this.load();
        }, methods: {
            loadTopPlayedTracks: function () {
                var self = this;
                self.loading = true;
                self.errors = false;
                self.items = [];
                spieldoseAPI.getTopPlayedTracks(this.interval, self.artist, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                        self.loading = false;
                    } else {
                        self.loading = false;
                        self.errors = false;
                    }
                });
            }, loadTopPlayedArtists: function () {
                var self = this;
                self.loading = true;
                self.errors = false;
                self.items = [];
                spieldoseAPI.getTopPlayedArtists(this.interval, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                        self.loading = false;
                    } else {
                        self.loading = false;
                        self.errors = false;
                    }
                });
            }, loadTopPlayedGenres: function () {
                var self = this;
                self.loading = true;
                self.errors = false;
                self.items = [];
                spieldoseAPI.getTopPlayedGenres(this.interval, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                        self.loading = false;
                    } else {
                        self.loading = false;
                        self.errors = false;
                    }
                });
            }, changeInterval: function (i) {
                if (this.interval != i) {
                    this.interval = i;
                }
                this.load();
            }, load: function () {
                switch (this.type) {
                    case "topTracks":
                        this.loadTopPlayedTracks();
                        break;
                    case "topArtists":
                        this.loadTopPlayedArtists();
                        break;
                    case "topGenres":
                        this.loadTopPlayedGenres();
                        break;
                }
            }, playTrack: function (track) {
                this.playerData.replace([track]);
            }, enqueueTrack: function (track) {
                this.playerData.enqueue([track]);
            }
        },
        props: ['type', 'title', 'listItemCount', 'showPlayCount', 'artist']
    });

    return (module);
})();