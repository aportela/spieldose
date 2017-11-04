"use strict";

var vTemplateBrowseAlbums = function () {
    return `
    <section class="section" id="section-albums">
        <div v-show="! loading">
            <spieldose-pagination v-bind:data="pager"></spieldose-pagination>
            <div class="album_item" v-for="album in albums">
                <a class="play_album" v-on:click="enqueueAlbumTracks(album.name, album.artist)">
                    <img class="album_cover" v-if="album.image" v-bind:src="album.albumCoverUrl"/>
                    <img class="album_cover" v-else="" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="/>
                    <i class="fa fa-play fa-4x"></i>
                    <img class="vynil no_cover" src="http://fc08.deviantart.net/fs17/f/2007/170/9/8/Vinyl_Disc_Icon_Updated_by_jordygreen.png" />
                </a>
                <div class="album_info">
                    <p class="album_name" title="">{{ album.name }}</p>
                    <p class="artist_name" title=""><a class="view_artist" v-bind:href="'/#/app/artist/' + album.artist">by {{ album.albumartist ? album.albumartist: album.artist }} ({{ album.year }})</a></p>
                </div>
            </div>
        </div>
    </section>
    `;
}

var browseAlbums = Vue.component('spieldose-browse-albums', {
    template: vTemplateBrowseAlbums(),
    data: function () {
        return ({
            loading: false,
            albums: [],
            pager: getPager()
        });
    },
    watch: {
        '$route'(to, from) {
            this.pager.actualPage = parseInt(to.params.page);
            this.search("");
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
        this.search("");
    }, methods: {
        search: function (text) {
            var self = this;
            self.loading = true;
            var d = {
                actualPage: parseInt(self.pager.actualPage),
                resultsPage: parseInt(self.pager.resultsPage)
            };
            if (text) {
                d.text = text;
            }
            jsonHttpRequest("POST", "/api/album/search", d, function (httpStatusCode, response) {
                for (var i = 0; i < response.albums.length; i++) {
                    if (response.albums[i].image) {
                        response.albums[i].albumCoverUrl = response.albums[i].image;
                    } else {
                        response.albums[i].albumCoverUrl = "#";
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
        enqueueAlbumTracks: function (album, artist) {
            bus.$emit("searchIntoPlayList", 1, DEFAULT_SECTION_RESULTS_PAGE, null, artist, album, null);
        }
    }
});
