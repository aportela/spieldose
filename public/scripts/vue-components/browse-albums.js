"use strict";

var vTemplateBrowseAlbums = function () {
    return `
    <div class="container is-fluid box">
        <p class="title is-1 has-text-centered">Browse albums</i></p>
        <div class="field">
            <div class="control has-icons-left" v-bind:class="loading ? 'is-loading': ''">
                <input class="input " v-model="nameFilter" type="text" placeholder="search album name..." v-on:keyup.esc="abortInstantSearch();" v-on:keyup="instantSearch();">
                <span class="icon is-small is-left">
                    <i class="fa fa-search"></i>
                </span>
            </div>
        </div>
        <spieldose-pagination v-bind:loading="loading" v-bind:data="pager" v-show="albums.length > 0"></spieldose-pagination>
        <div class="browse-album-item" v-for="album in albums" v-show="! loading">
            <a class="play-album" v-on:click="enqueueAlbumTracks(album.name, album.artist)" v-bind:title="'click to play album'">
                <img class="album-thumbnail" v-if="album.image" v-bind:src="album.image"/>
                <img class="album-thumbnail" v-else="" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="/>
                <i class="fa fa-play fa-4x"></i>
                <img class="vinyl no-cover" src="http://fc08.deviantart.net/fs17/f/2007/170/9/8/Vinyl_Disc_Icon_Updated_by_jordygreen.png" />
            </a>
            <div class="album-info">
                <p class="album-name">{{ album.name }}</p>
                <p class="artist-name"><a v-bind:title="'click to open artist section'" v-bind:href="'/#/app/artist/' + $router.encodeSafeName(album.artist)">by {{ album.albumartist ? album.albumartist: album.artist }}</a><span v-show="album.year"> ({{ album.year }})</span></p>
            </div>
        </div>
        <div class="is-clearfix"></div>
    </div>
    `;
}

var browseAlbums = Vue.component('spieldose-browse-albums', {
    template: vTemplateBrowseAlbums(),
    data: function () {
        return ({
            loading: false,
            nameFilter: null,
            timeout: null,
            albums: [],
            pager: getPager(),
            playerData: sharedPlayerData,
        });
    },
    watch: {
        '$route'(to, from) {
            if (to.name == "albums" || to.name == "albumsPaged") {
                this.pager.actualPage = parseInt(to.params.page);
                this.search();
            }
        }
    },
    created: function () {
        var self = this;
        this.pager.refresh = function () {
            self.$router.push({ name: 'albumsPaged', params: { page: self.pager.actualPage } });
        }
        if (this.$route.params.page) {
            self.pager.actualPage = parseInt(this.$route.params.page);
        }
        this.search();
    }, methods: {
        abortInstantSearch: function () {
            this.nameFilter = null;
        },
        instantSearch: function () {
            var self = this;
            if (self.timeout) {
                clearTimeout(self.timeout);
            }
            self.timeout = setTimeout(function () {
                self.pager.actualPage = 1;
                self.search();
            }, 256);
        },
        search: function () {
            var self = this;
            self.loading = true;
            var d = {
                actualPage: parseInt(self.pager.actualPage),
                resultsPage: parseInt(self.pager.resultsPage)
            };
            if (self.nameFilter) {
                d.text = self.nameFilter;
            }
            jsonHttpRequest("POST", "/api/album/search", d, function (httpStatusCode, response) {
                for (var i = 0; i < response.albums.length; i++) {
                    if (response.albums[i].image) {
                        response.albums[i].albumCoverUrl = response.albums[i].image;
                    } else {
                        response.albums[i].albumCoverUrl = null;
                    }
                }
                if (response.albums.length > 0) {
                    self.pager.totalPages = response.totalPages;
                } else {
                    self.pager.totalPages = 0;
                }
                if (self.pager.actualPage < self.pager.totalPages) {
                    self.pager.nextPage = self.pager.actualPage + 1;
                } else {
                    self.pager.nextPage = self.pager.totalPages;
                }
                self.albums = response.albums;
                self.loading = false;
            });
        },
        getAlbumTracks: function (album, artist, callback) {
            var d = {
                artist: artist,
                album: album
            };
            jsonHttpRequest("POST", "/api/track/search", d, function (httpStatusCode, response) {
                callback(response.tracks);
            });

        },
        enqueueAlbumTracks: function (album, artist) {
            var self = this;
            this.getAlbumTracks(album, artist, function (tracks) {
                self.playerData.emptyPlayList();
                self.playerData.tracks = tracks;
                self.playerData.play();
            });
        }
    }
});
