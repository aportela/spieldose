"use strict";

var vTemplateBrowseArtist = function () {
    return `
    <section v-show="section == '#/artist'" class="section" id="section-artist">
        <h1>{{ artist.name }}</h1>
        <div v-if="artist" v-bind="artist" class="container">
            <img v-if="artist.image" v-bind:src="artist.image" class="artist-thumbnail is-pulled-left">
            <div class="content is-clearfix" id="bio" v-if="artist.bio" v-html="artist.bio"></div>
            <div class="panel">
                <h2>Albums: <i class="is-unselectable fa fa-2x fa-th-large" v-on:click.prevent="hideAlbumDetails()"></i><i class="is-unselectable fa fa-2x fa-list-ol" v-on:click.prevent="showAlbumDetails()"></i></h2>
                <div class="album_item" v-for="album in artist.albums">
                    <a class="play_album" v-on:click="playAlbum(album.name, artist.name)">
                        <img class="album_cover" v-if="album.image" v-bind:src="album.image"/>
                        <img class="album_cover" v-else="" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="/>
                        <i class="fa fa-play fa-4x"></i>
                        <img class="vynil no_cover" src="http://fc08.deviantart.net/fs17/f/2007/170/9/8/Vinyl_Disc_Icon_Updated_by_jordygreen.png" />
                    </a>
                    <div class="album_info">
                        <p class="album_name" title="">{{ album.name }} ({{ album.year }})</p>
                    </div>
                    <!--
                    <ul v-if="detailedView">
                        <li v-for="track in album.tracks"><a>{{ track.title }} ({{ track.playtimeString }})</a></li>
                    </ul>
                    -->
                </div>
            </div>
        </div>
    </section>
    `;
}

var browseArtist = Vue.component('spieldose-browse-artist', {
    template: vTemplateBrowseArtist(),
    data: function () {
        return ({
            artist: {},
            detailedView: false,
        });
    }, props: ['section'
    ], created: function () {
        var self = this;
        bus.$on("loadArtist", function (artist) {
            self.getArtist(artist);
        });
    }, methods: {
        getArtist: function (artist) {
            var self = this;
            var d = {};
            jsonHttpRequest("GET", "/api/artist/" + encodeURIComponent(artist), d, function (httpStatusCode, response) {
                self.artist = response.artist;
                if (self.artist.bio) {
                    self.artist.bio = self.artist.bio.replace(/(?:\r\n|\r|\n)/g, '<br />')
                }
            });
        },
        playAlbum: function (album, artist) {
            bus.$emit("searchIntoPlayList", 1, DEFAULT_SECTION_RESULTS_PAGE, null, artist, album, null);
        },
        hideAlbumDetails: function () {
            this.detailedView = false;
        }, showAlbumDetails: function () {
            this.detailedView = true;
        }
    }
});
