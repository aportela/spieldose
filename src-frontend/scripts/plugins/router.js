import { createWebHashHistory, createRouter } from "vue-router";

import { default as upgrade } from '../pages/upgrade-database.js';
import { default as signInUp } from '../pages/signin.js';
import { default as container } from '../layouts/app.js';
import { default as search } from '../vue-components/search.js';
import { default as dashboard } from '../vue-components/dashboard.js';
//import { default as nowPlaying } from '../vue-components/playlists.js';
import { default as currentPlaylist } from '../pages/currentPlaylist.js';
import { default as browseArtists } from '../vue-components/browse-artists.js';
import { default as browseAlbums } from '../vue-components/browse-albums.js';
import { default as browsePaths } from '../vue-components/browse-paths.js';
import { default as browsePlaylists } from '../vue-components/browse-playlists.js';
import { default as browseRadioStations } from '../vue-components/browse-radio-stations.js';
import { default as browseArtist } from '../vue-components/browse-artist.js';

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
                component: currentPlaylist
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
                path: 'artist/:name',
                name: 'artistPage',
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
const router = createRouter({
    history: createWebHashHistory(),
    routes
});

export default router;
