import { default as pagination } from '../vue-components/pagination.js';

const template = function () {
    return `
        <div class="field has-addons">
            <div class="control has-icons-left is-expanded is-small" :class="{ 'is-loading': loading }">
                <input class="input is-small" type="text" placeholder="Search" v-model.trim="searchQuery"
                    v-on:keyup.enter="onSearch" :disabled="loading">
                <span class="icon is-small is-left">
                    <i class="fas fa-search"></i>
                </span>
                <span class="icon is-small is-right">
                    <i class="fas fa-loading"></i>
                </span>
            </div>
            <div class="control">
                <div class="select is-small">
                    <select :disabled="loading">
                        <option value="">sort by name</option>
                        <option value="">sort by recent</option>
                        <option value="">sort by popular</option>
                    </select>
                </div>
            </div>
            <div class="control">
                <div class="select is-small">
                    <select v-model="sort.order" :disabled="loading">
                        <option value="ASC">ASCending order</option>
                        <option value="DESC">DESCending order</option>
                    </select>
                </div>
            </div>
            <div class="control">
                <div class="select is-small">
                    <select v-model.number="pager.resultsPage" :disabled="loading">
                        <option value="32">32 results/page</option>
                        <option value="64">64 results/page</option>
                        <option value="128">128 results/page</option>
                        <option value="0">all results</option>
                    </select>
                </div>
            </div>
            <div class="control">
                <button class="button is-small is-pink" @click.prevent="onSearch" :disabled="loading">Search</button>
            </div>
        </div>
        <spieldose-pagination :disabled="loading" :data="pager" v-on:pagination-changed="onPaginationChanged"></spieldose-pagination>
        <div>
            <div class="sp-artist-thumbnail-link is-pulled-left is-size-6" v-for="album, idx in albums" :key="idx">
                <a class="is-size-7">
                    <img src="https://lastfm.freetls.fastly.net/i/u/300x300/2a96cbd8b46e442fc41c2b86b821562f.png">
                    <p>{{ album.name }}</p>
                    <p v-if="album.albumArtist">{{ album.albumArtist }} <span v-if="album.year">({{ album.year }})</span></p>
                    <p v-else-if="album.artist">{{ album.artist }}</p>
                </a>
            </div>
        </div>
    `;
};

export default {
    name: 'spieldose-page-browse-albums',
    template: template(),
    data: function () {
        return ({
            loading: false,
            searchQuery: null,
            resetPager: false,
            pager: {
                currentPage: 1,
                resultsPage: 32,
                totalPages: 0,
                totalResults: 0
            },
            sort: {
                field: 'name',
                order: 'ASC'
            },
            albums: []
        });
    },
    watch: {
        searchQuery: function () {
            this.resetPager = true;
        }
    },
    created: function () {
        this.onSearch();
    },
    components: {
        'spieldose-pagination': pagination
    },
    methods: {
        onSearch: function () {
            this.loading = true;
            if (this.resetPager) {
                this.pager.currentPage = 1;
                this.resetPager = false;
            }
            this.$api.album.search(this.searchQuery, this.pager.currentPage, this.pager.resultsPage, this.sort.field, this.sort.order).then(success => {
                this.loading = false;
                this.albums = success.data.results.items;
                this.pager = success.data.results.pager;
            }).catch(error => {
                this.loading = false;
            });
        },
        onPaginationChanged: function (event) {
            this.onSearch();
        }
    }
}