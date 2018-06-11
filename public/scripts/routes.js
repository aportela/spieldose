"use strict";

/**
 * vue-router route definitions
 */
const routes = [
    { path: '/upgrade', name: 'upgrade', component: upgrade },
    { path: '/signin', name: 'signin', component: signInUp },
    {
        path: '/app',
        component: container,
        children: [
            {
                path: 'search',
                name: 'search',
                component: search
            },
            {
                path: 'dashboard',
                name: 'dashboard',
                component: dashboard
            },
            {
                path: 'now_playing',
                name: 'nowPlaying',
                component: nowPlaying
            },
            {
                path: 'artists',
                name: 'artists',
                component: browseArtists,
                children: [
                    {
                        path: 'page/:page',
                        name: 'artistsPaged',
                        component: browseArtists
                    }
                ]
            },
            {
                path: 'albums',
                name: 'albums',
                component: browseAlbums,
                children: [
                    {
                        path: 'page/:page',
                        name: 'albumsPaged',
                        component: browseAlbums
                    }
                ]
            },
            {
                path: 'paths',
                name: 'paths',
                component: browsePaths,
                children: [
                    {
                        path: 'page/:page',
                        name: 'pathsPaged',
                        component: browsePaths
                    }
                ]
            },
            {
                path: 'playlists',
                name: 'playlists',
                component: browsePlaylists,
                children: [
                    {
                        path: 'page/:page',
                        name: 'playlistsPaged',
                        component: browsePlaylists
                    }
                ]
            },
            {
                path: 'radio_stations',
                name: 'radioStations',
                component: browseRadioStations,
                children: [
                    {
                        path: 'page/:page',
                        name: 'radioStationsPaged',
                        component: browseRadioStations
                    }
                ]
            },
            {
                path: 'artist/:artist',
                name: 'artist',
                component: browseArtist,
                children: [
                    {
                        path: 'bio',
                        name: 'artistBio',
                        component: browseArtist
                    },
                    {
                        path: 'tracks',
                        name: 'artistTracks',
                        component: browseArtist,
                        children: [
                            {
                                path: 'page/:page',
                                name: 'artistTracksPaged',
                                component: browseArtist
                            }
                        ]
                    },
                    {
                        path: 'albums',
                        name: 'artistAlbums',
                        component: browseArtist
                    },
                    {
                        path: 'update',
                        name: 'artistUpdate',
                        component: browseArtist
                    }
                ]
            }]
    }
];

/**
 * main vue-router component inicialization
 */
const router = new VueRouter({
    routes
});

/**
 * top scroll window before change router page
 */
router.beforeEach((to, from, next) => {
    window.scrollTo(0, 0);
    next();
});
