"use strict";

var vTemplateBrowseAlbums = function () {
    return `
    <section v-show="section == '#/albums'" class="section" id="section-albums">
        <spieldose-pagination v-bind:searchEvent="'browseAlbums'"></spieldose-pagination>
        <div class="album_item" v-for="album in albums">
            <a class="play_album" v-on:click="enqueueAlbumTracks(album.name, album.artist)">
                <img class="album_cover" v-if="album.image" v-bind:src="album.albumCoverUrl"/>
                <img class="album_cover" v-else="" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="/>
                <i class="fa fa-play fa-4x"></i>
                <img class="vynil no_cover" src="http://fc08.deviantart.net/fs17/f/2007/170/9/8/Vinyl_Disc_Icon_Updated_by_jordygreen.png" />
            </a>
            <div class="album_info">
                <p class="album_name" title="">{{ album.name }}</p>
                <p class="artist_name" title=""><a class="view_artist"  href="api/artist/get.php?id=">by {{ album.albumartist ? album.albumartist: album.artist }} ({{ album.year }})</a></p>
            </div>
        </div>
    </section>
    `;
}

var browseAlbums = Vue.component('spieldose-browse-albums', {
    template: vTemplateBrowseAlbums(),
    data: function () {
        return ({
            albums: [],
            pager: {
                actualPage: 1,
                previousPage: 1,
                nextPage: 1,
                totalPages: 0,
                resultsPage: DEFAULT_SECTION_RESULTS_PAGE
            }
        });
    }, props: ['section'
    ], computed: {
    }, created: function () {
        var self = this;
        bus.$on("browseAlbums", function (text, page, resultsPage) {
            self.search(text, page, resultsPage);
        });
    }, methods: {
        search: function (text, page, resultsPage) {
            var self = this;
            self.pager.actualPage = page;
            if (page > 1) {
                self.pager.previousPage = page - 1;
            } else {
                self.pager.previousPage = 1;
            }
            var d = {
                actualPage: self.pager.actualPage,
                resultsPage: self.pager.resultsPage
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
                if (page < self.pager.totalPages) {
                    self.pager.nextPage = page + 1;
                } else {
                    self.pager.nextPage = self.pager.totalPages;
                }
                self.albums = response.albums;
                bus.$emit("updatePager", self.pager.actualPage, self.pager.totalPages, self.pager.totalResults);
            });
        },
        enqueueAlbumTracks: function (album, artist) {
            bus.$emit("searchIntoPlayList", 1, DEFAULT_SECTION_RESULTS_PAGE, null, artist, album, null);
        }
    }
});
