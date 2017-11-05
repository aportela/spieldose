"use strict";

var vTemplateBrowseArtist = function () {
    return `
    <article class="media container is-fluid box" v-show="! loading">
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
                        <spieldose-dashboard-toplist v-if="activeTab == 'overview'" v-bind:type="'topTracks'" v-bind:title="'Top played tracks'" v-bind:listItemCount="10" v-bind:showPlayCount="true" v-bind:artist="this.$route.params.artist"></spieldose-dashboard-toplist>
                    </div>
                </div>
            </div>
            <div class="panel" v-if="activeTab == 'bio'" v-html="">
                <div class="content is-clearfix" id="bio" v-html="artist.bio"></div>
            </div>
            <div class="panel" v-if="activeTab == 'albums'">
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
    </article>
    `;
}

var browseArtist = Vue.component('spieldose-browse-artist', {
    template: vTemplateBrowseArtist(),
    data: function () {
        return ({
            loading: false,
            artist: {},
            activeTab: 'overview',
            truncatedBio: null,
            detailedView: false,
        });
    }, props: ['section'],
    watch: {
        '$route'(to, from) {
            this.getArtist(to.params.artist);
        }
    }
    , created: function () {
        this.getArtist(this.$route.params.artist);
    }, methods: {
        getArtist: function (artist) {
            var self = this;
            self.loading = true;
            var d = {};
            jsonHttpRequest("GET", "/api/artist/" + encodeURIComponent(artist), d, function (httpStatusCode, response) {
                self.artist = response.artist;
                if (self.artist.bio) {
                    self.artist.bio = self.artist.bio.replace(/(?:\r\n|\r|\n)/g, '<br />');
                    self.truncatedBio = self.truncate(self.artist.bio);
                    self.activeTab = "overview";
                }
                self.loading = false;
            });
        },
        changeTab: function (tab) {
            this.activeTab = tab;
        },
        playAlbum: function (album, artist) {
            bus.$emit("searchIntoPlayList", 1, DEFAULT_SECTION_RESULTS_PAGE, null, artist, album, null);
        },
        hideAlbumDetails: function () {
            this.detailedView = false;
        }, showAlbumDetails: function () {
            this.detailedView = true;
        }, truncate: function (text) {
            return (text.substring(0, 500));
        }
    }
});
