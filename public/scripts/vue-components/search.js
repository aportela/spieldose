"use strict";

var vTemplateSearch = function () {
    return `
    <div class="field has-addons">
        <div class="control">
            <div class="select is-fullwidth">
                <select v-model="filterByTextOn">
                        <option value="">search on anything</option>
                        <option value="tracks">search on track names</option>
                        <option value="artists">search on artist names</option>
                        <option value="albums">search on album names</option>
                </select>
            </div>
        </div>
        <div class="control is-expanded">
            <input class="input" type="text" v-on:keyup.13="search()" v-model="filterByTextCondition" placeholder="type search text condition">
        </div>
        <div class="control">
            <a class="button" v-on:click.prevent="search()">
                <span class="icon is-small">
                <i class="fa fa-search"></i>
                </span>
                <span>Search</span>
            </a>
        </div>
    </div>
    `;
}

var search = Vue.component('spieldose-search', {
    template: vTemplateSearch(),
    data: function () {
        return ({
            filterByTextOn: "",
            filterByTextCondition: ""
        });
    }, props: ['section'
    ], methods: {
        search() {
            switch (this.filterByTextOn) {
                case "artists":
                    if (this.section != "#/artists") {
                        bus.$emit("activateSection", "#/artists");
                    }
                    bus.$emit("browseArtists", this.filterByTextCondition, 1, DEFAULT_SECTION_RESULTS_PAGE);
                    break;
                case "albums":
                    bus.$emit("activateSection", "#/albums");
                    bus.$emit("browseAlbums", this.filterByTextCondition, 1, DEFAULT_SECTION_RESULTS_PAGE);
                    break;
                default:
                    bus.$emit("activateSection", "#/search-results");
                    bus.$emit("globalSearch", this.filterByTextCondition, 1, DEFAULT_SECTION_RESULTS_PAGE);
                    break;
            }
        }
    }
});
