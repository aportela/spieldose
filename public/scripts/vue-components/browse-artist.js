"use strict";

var vTemplateBrowseArtist = function () {
    return `
    <div class="container is-fluid box">
        <p v-if="loading" class="title is-1 has-text-centered">Loading <i v-if="loading" class="fa fa-cog fa-spin fa-fw"></i></p>
        <p v-else="! loading" class="title is-1 has-text-centered">Artist details</p>
        <div class="media" v-if="! errors && ! loading">
            <figure class="image media-left">
                <img v-bind:src="artist.image" alt="Image" class="artist_avatar" v-if="artist.image">
                <img alt="Image" class="artist_avatar" src="https://cdn2.iconfinder.com/data/icons/app-types-in-grey/128/app_type_festival_512px_GREY.png" v-else />
            </figure>
            <div class="media-content is-light">
                <p class="title is-1">{{ artist.name }}</p>
                <p class="subtitle is-6" v-if="artist.playCount > 0">{{ artist.playCount }} plays</p>
                <p class="subtitle is-6" v-else>not played yet</p>
                <div class="tabs is-medium">
                    <ul>
                        <li v-bind:class="{ 'is-active' : activeTab == 'overview' }"><a href="#" v-on:click.prevent="changeTab('overview');">Overview</a></li>
                        <li v-bind:class="{ 'is-active' : activeTab == 'bio' }"><a href="#" v-on:click.prevent="changeTab('bio');">Bio</a></li>
                        <li v-bind:class="{ 'is-active' : activeTab == 'tracks' }"><a href="#" v-on:click.prevent="changeTab('tracks');">Tracks</a></li>
                        <li v-bind:class="{ 'is-active' : activeTab == 'albums' }"><a href="#" v-on:click.prevent="changeTab('albums');">Albums</a></li>
                    </ul>
                </div>
                <div class="panel" v-if="activeTab == 'overview'">
                    <div class="content is-clearfix" id="bio" v-if="artist.bio" v-html="truncatedBio"></div>
                    <div class="columns">
                        <div class="column is-half is-full-mobile">
                            <spieldose-dashboard-toplist v-if="activeTab == 'overview' && artist.name" v-bind:type="'topTracks'" v-bind:title="'Top played tracks'" v-bind:listItemCount="10" v-bind:showPlayCount="true" :key="$route.params.artist" v-bind:artist="$route.params.artist"></spieldose-dashboard-toplist>
                        </div>
                    </div>
                </div>
                <div class="panel" v-if="activeTab == 'bio'" v-html="">
                    <div class="content is-clearfix" id="bio" v-html="artist.bio"></div>
                </div>
                <div class="panel" v-if="activeTab == 'albums'">
                    <div class="browse-album-item" v-for="album in artist.albums" v-show="! loading">
                        <a class="play-album" v-on:click="enqueueAlbumTracks(album.name, album.artist, album.year)" v-bind:title="'click to play album'">
                            <img class="album-thumbnail" v-if="album.image" v-bind:src="album.image"/>
                            <img class="album-thumbnail" v-else="" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="/>
                            <i class="fa fa-play fa-4x"></i>
                            <img class="vinyl no-cover" src="http://fc08.deviantart.net/fs17/f/2007/170/9/8/Vinyl_Disc_Icon_Updated_by_jordygreen.png" />
                        </a>
                        <div class="album-info">
                            <p class="album-name">{{ album.name }}</p>
                            <p class="album-year" v-show="album.year">({{ album.year }})</p>
                        </div>
                    </div>
                    <div class="is-clearfix"></div>
                </div>
            </div>
        </div>
        <spieldose-api-error-component v-if="errors" v-bind:apiError="apiError"></spieldose-api-error-component>
    </div>
    `;
}

var browseArtist = Vue.component('spieldose-browse-artist', {
    template: vTemplateBrowseArtist(),
    data: function () {
        return ({
            loading: false,
            errors: false,
            apiError: null,
            artist: {},
            activeTab: 'overview',
            truncatedBio: null,
            detailedView: false,
            playerData: sharedPlayerData,
        });
    },
    watch: {
        '$route'(to, from) {
            if (to.name == "artist") {
                this.getArtist(to.params.artist);
            }
        }
    }
    , created: function () {
        this.getArtist(this.$route.params.artist);
    }, methods: {
        getArtist: function (artist) {
            var self = this;
            self.loading = true;
            self.errors = false;
            var d = {};
            spieldoseAPI.getArtist(artist, function(response) {
                if (response.ok) {
                    self.artist = response.body.artist;
                    if (self.artist.bio) {
                        self.artist.bio = self.artist.bio.replace(/(?:\r\n|\r|\n)/g, '<br />');
                        self.truncatedBio = self.truncate(self.artist.bio);
                        self.activeTab = "overview";
                    }
                    self.loading = false;
                } else {
                    self.errors = true;
                    self.apiError = response.getApiErrorData();
                    self.loading = false;
                }
            });
        },
        changeTab: function (tab) {
            this.activeTab = tab;
        }, truncate: function (text) {
            return (text.substring(0, 500));
        },
        enqueueAlbumTracks: function (album, artist, year) {
            var self = this;
            spieldoseAPI.getAlbumTracks(album || null, artist || null , year || null, function(response) {
                self.playerData.emptyPlayList();
                if (response.ok) {
                    if (response.body.tracks && response.body.tracks.length > 0) {
                        self.playerData.tracks = response.body.tracks;
                        self.playerData.play();
                    }
                } else {
                    self.errors = true;
                    self.apiError = response.getApiErrorData();
                }
            });
        }
    }
});
