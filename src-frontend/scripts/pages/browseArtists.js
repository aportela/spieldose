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
                    <select>
                        <option value="">sort by name</option>
                        <option value="">sort by recent</option>
                        <option value="">sort by popular</option>
                    </select>
                </div>
            </div>
            <div class="control">
                <div class="select is-small">
                    <select>
                        <option value="">ASCending order</option>
                        <option value="">DESCending order</option>
                    </select>
                </div>
            </div>
            <div class="control">
                <div class="select is-small">
                    <select>
                        <option value="">32 results/page</option>
                        <option value="">64 results/page</option>
                        <option value="">128 results/page</option>
                        <option value="">all results</option>
                    </select>
                </div>
            </div>
            <div class="control">
                <button class="button is-small is-pink" @click.prevent="onSearch" :disabled="loading">Search</button>
            </div>
        </div>
        <div>
            <div class="sp-artist-thumbnail-link is-pulled-left is-size-6" v-for="artist in artists" :key="artist.name">
                <router-link :to="{ name: 'artistPage', params: { name: artist.name } }">
                    <img src="https://lastfm.freetls.fastly.net/i/u/300x300/2a96cbd8b46e442fc41c2b86b821562f.png">
                    <p>{{ artist.name }}</p>
                </router-link>
            </div>
        </div>
    `;
};

export default {
    name: 'spieldose-page-browse-artists',
    template: template(),
    data: function () {
        return ({
            loading: false,
            searchQuery: null,
            artists: []
        });
    },
    created: function () {
        this.onSearch();
    },
    methods: {
        onSearch: function () {
            this.loading = true;
            this.tracks = [];
            this.currentTrackIndex = -1;
            this.$api.artist.search(this.searchQuery).then(success => {
                this.loading = false;
                this.artists = success.data.results.items;
            }).catch(error => {
                this.loading = false;
            });
        }
    }
}