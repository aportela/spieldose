let dashboardToplist = (function () {
    "use strict";

    const template = function () {
        return `
            <section class="panel chart">
                <p class="panel-heading">
                    <span class="icon">
                        <i class="fas fa-cog fa-spin fa-fw" v-if="loading"></i>
                        <i class="fas fa-exclamation-triangle" v-else-if="hasAPIErrors"></i>
                        <i class="fas fa-list" v-else></i>
                    </span>
                    <span>{{ title }}</span>
                    <a class="icon is-pulled-right" title="refresh data" v-on:click.prevent="load();"><i class="fas fa-redo fa-fw2"></i></a>
                </p>
                <p class="panel-tabs">
                    <a v-bind:class="{ 'is-active' : isAllTimeInterval }" v-on:click.prevent="changeInterval(0);">All Time</a>
                    <a v-bind:class="{ 'is-active' : isPastWeekInterval }" v-on:click.prevent="changeInterval(1);">Past week</a>
                    <a v-bind:class="{ 'is-active' : isPastMonthInterval }" v-on:click.prevent="changeInterval(2);">Past month</a>
                    <a v-bind:class="{ 'is-active' : isPastSemesterInterval }" v-on:click.prevent="changeInterval(3);">Past semester</a>
                    <a v-bind:class="{ 'is-active' : isPastYearInterval }" v-on:click.prevent="changeInterval(4);">Past Year</a>
                </p>
                <div class="panel-block cut-text">
                    <ol v-if="items.length > 0">
                        <li class="is-small" v-if="isTopTracksType" v-for="item in items" v-bind:key="item.id">
                            <i v-on:click="playTrack(item);" class="cursor-pointer fa fa-play" title="play this track"></i>
                            <i v-on:click="enqueueTrack(item);" class="cursor-pointer fa fa-plus-square" title="enqueue this track"></i>
                            <span>{{ item.title }}</span>
                            <span v-if="item.artist"> / <a v-on:click.prevent="navigateToArtistPage(item.artist);">{{ item.artist }}</a></span>
                            <span v-if="showPlayCount"> ({{ item.total }} plays)</span>
                        </li>
                        <li class="is-small" v-if="isTopArtistsType" v-for="item in items">
                            <a v-on:click.prevent="navigateToArtistPage(item.artist);">{{ item.artist }}</a>
                            <span v-if="showPlayCount"> ({{ item.total }} plays)</span>
                        </li>
                        <li class="is-small" v-if="isTopGenresType" v-for="item in items">
                            <span>{{ item.genre }}</span>
                            <span v-if="showPlayCount"> ({{ item.total }} plays)</span>
                        </li>
                    </ol>
                    <p v-else-if="! hasItems && ! loading && ! hasAPIErrors">not enough data for the stats</p>
                    <p v-else-if="hasAPIErrors">error loading data (invalid response from server)</p>
                </div>
            </section>
        `;
    };

    /* top played (track/artist/genres) component */
    let module = Vue.component('spieldose-dashboard-toplist', {
        template: template(),
        mixins: [mixinAPIError, mixinTopRecentCharts, mixinPlayer],
        data: function () {
            return ({
                loading: false,
                activeInterval: 0
            });
        },
        props: [
            'type', 'title', 'listItemCount', 'showPlayCount', 'artist'
        ],
        computed: {
            isAllTimeInterval: function () {
                return (this.activeInterval == 0);
            },
            isPastWeekInterval: function () {
                return (this.activeInterval == 1);
            },
            isPastMonthInterval: function () {
                return (this.activeInterval == 2);
            },
            isPastSemesterInterval: function () {
                return (this.activeInterval == 3);
            },
            isPastYearInterval: function () {
                return (this.activeInterval == 4);
            },
            isTopTracksType: function () {
                return (this.type == 'topTracks');
            },
            isTopArtistsType: function () {
                return (this.type == 'topArtists');
            },
            isTopGenresType: function () {
                return (this.type == 'topGenres');
            }
        }, methods: {
            loadTopPlayedTracks: function () {
                let self = this;
                spieldoseAPI.getTopPlayedTracks(this.interval, self.artist, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.loading = false;
                });
            }, loadTopPlayedArtists: function () {
                let self = this;
                spieldoseAPI.getTopPlayedArtists(this.interval, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.loading = false;
                });
            }, loadTopPlayedGenres: function () {
                let self = this;
                spieldoseAPI.getTopPlayedGenres(this.interval, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.loading = false;
                });
            }, changeInterval: function (interval) {
                if (!this.loading) {
                    if (interval && this.activeInterval != interval) {
                        this.activeInterval = interval;
                        this.load();
                    }
                }
            }, load: function () {
                this.clearAPIErrors();
                this.loading = true;
                this.items = [];
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
                    default:
                        this.loading = false;
                        break;
                }
            }
        }
    });

    return (module);
})();