const template = function () {
    return `
        <nav id="menu" class="panel is-unselectable">
            <p class="panel-heading">{{ $t("menu.labels.header") }}</p>
            <router-link :to="{ name: 'dashboard' }" :class="{'panel-block': true, 'is-active': isSectionActive('dashboard') }">
                <span class="panel-icon"><i class="fas fa-chart-line"></i></span>
                <span>{{ $t("menu.labels.dashboard") }}</span>
            </router-link>
            <router-link :to="{ name: 'nowPlaying' }" :class="{'panel-block': true, 'is-active': isSectionActive('nowPlaying') }">
                <span class="panel-icon"><i class="fas fa-headphones"></i></span>
                <span>{{ $t("menu.labels.currentPlaylist") }}</span>
            </router-link>
            <!--
            <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('search') }" v-on:click.prevent="changeSection('search');">
                <span class="panel-icon"><i class="fas fa-search"></i></span>
                <span>{{ $t("menu.labels.search") }}</span>
            </a>
            -->
            <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('artists') }" v-on:click.prevent="changeSection('artists');">
                <span class="panel-icon"><i class="fas fa-user"></i></span>
                <span>{{ $t("menu.labels.browseArtists") }}</span>
            </a>
            <!--
            <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('albums') }" v-on:click.prevent="changeSection('albums');">
                <span class="panel-icon"><i class="fas fa-circle"></i></span>
                <span>{{ $t("menu.labels.browseAlbums") }}</span>
            </a>
            -->
            <router-link :to="{ name: 'browsePaths' }" :class="{'panel-block': true, 'is-active': isSectionActive('paths') }">
                <span class="panel-icon"><i class="fas fa-folder-open"></i></span>
                <span>{{ $t("menu.labels.browsePaths") }}</span>
            </router-link>
            <!--
            <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('playlists') }" v-on:click.prevent="changeSection('playlists');">
                <span class="panel-icon"><i class="fas fa-list-alt"></i></span>
                <span>{{ $t("menu.labels.browsePlaylists") }}</span>
            </a>
            <a class="panel-block" v-bind:class="{ 'is-active': isSectionActive('radioStations') }" v-on:click.prevent="changeSection('radioStations');">
                <span class="panel-icon"><i class="fas fa-broadcast-tower"></i></span>
                <span>{{ $t("menu.labels.browseRadioStations") }}</span>
            </a>
            <a class="panel-block" v-on:click.prevent="signOut();">
                <span class="panel-icon"><i class="fa-solid fa-sun"></i></span>
                <span>Toggle dark mode</span>
            </a>
            -->
            <router-link :to="{ name: 'profile' }" :class="{'panel-block': true, 'is-active': isSectionActive('profile') }">
                <span class="panel-icon"><i class="fas fa-user"></i></span>
                <span>{{ $t("menu.labels.profile") }}</span>
            </router-link>
            <a class="panel-block" v-on:click.prevent="signOut();">
                <span class="panel-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span>{{ $t("menu.labels.signOut") }}</span>
            </a>
        </nav>
    `;
};

export default {
    name: 'spieldose-menu-component',
    template: template(),
    methods: {
        isSectionActive: function (section) {
            return (this.$route.name == section);
        },
        changeSection: function (routeName) {
            this.$router.push({ name: routeName }).catch(err => { });
        },
        signOut: function (e) {
            this.$api.session.signOut().then(response => {
                this.$localStorage.remove("jwt");
                this.$router.push({ name: 'signin' });
            }).catch(error => { console.log(error); });
        }
    }
}