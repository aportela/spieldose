"use strict";

const DEFAULT_SECTION_RESULTS_PAGE = 32;

/* global object for events between vuejs components */
const bus = new Vue();

var getPager = function () {
    return ({
        actualPage: 1,
        previousPage: 1,
        nextPage: 1,
        totalPages: 0,
        resultsPage: DEFAULT_SECTION_RESULTS_PAGE
    });
}

const routes = [
    { path: '/signin', name: 'signin', component: signIn },
    {
        path: '/app',
        component: container,
        children: [
            {
                path: 'dashboard',
                name: 'dashboard',
                component: dashboard
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
                path: 'artist/:artist',
                name: 'artist',
                component: browseArtist
            }]
    }
];

const router = new VueRouter({
    routes
});

const app = new Vue({
    router,
    data: function () {
        return ({
            xhr: false,
            logged: true
        });
    },
    created: function () {
        var self = this;
        bus.$on("signOut", function () {
            self.signout();
        });
        bus.$on("changeRouterPath", function (routeName) {
            self.$router.push({ name: routeName });
        });
        this.poll(function (logged2) {
            if (!logged2) {
                self.$router.push({ name: 'signin' });
            } else {
                self.$router.push({ name: 'dashboard' });
            }
        });
    },
    methods: {
        signout: function () {
            var self = this;
            self.xhr = true;
            jsonHttpRequest("GET", "/api/user/signout", {}, function (httpStatusCode, response, originalResponse) {
                self.xhr = false;
                self.logged = false;
                switch (httpStatusCode) {
                    case 200:
                        self.$router.push({ path: '/signin' });
                        break;
                    default:
                        bus.$emit("showModal", "Error", "Invalid server response: " + httpStatusCode + "\n" + originalResponse);
                        break;
                }
            });
        },
        poll: function (callback) {
            var self = this;
            self.xhr = true;
            jsonHttpRequest("GET", "/api/user/poll", {}, function (httpStatusCode, response, originalResponse) {
                self.xhr = false;
                callback(httpStatusCode == 200);
            });
        }
    }
}).$mount('#app');

