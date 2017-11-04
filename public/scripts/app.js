"use strict";

const DEFAULT_SECTION_RESULTS_PAGE = 32;

/* global object for events between vuejs components */
const bus = new Vue();

/*
var app = new Vue({
    el: "#app",
    data: function () {
        return ({
            logged: true,
            xhr: false
        });
    },
    created: function () {
        var self = this;
        bus.$on("signOut", function (hash) {
            self.signout();
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
                        window.location.href = "/login";
                        break;
                    default:
                        bus.$emit("showModal", "Error", "Invalid server response: " + httpStatusCode + "\n" + originalResponse);
                        break;
                }
            });
        }
    }
});
*/

const routes = [
    { path: '/signin', component: signIn },
    {
        path: '/app',
        component: container,
        children: [
        {
            path: 'dashboard',
            component: dashboard
        },
        {
            path: 'artists',
            component: browseArtists,
            props: true
        },
        {
            path: 'albums',
            component: browseAlbums,
            props: true
        },
        {
            path: 'artist/:artist',
            component: browseArtist,
            props: true
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
            logged: true
        });
    },
    created: function () {
        if (!this.logged) {
            this.$router.push({ path: '/signin' });
        } else {
            //this.$router.push({ path: '/app/dashboard' });
        }
        var self = this;
        bus.$on("signOut", function() {
            self.signout();
        });
        bus.$on("changeRouterPath", function (newPath) {
            self.$router.push({ path: newPath });
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
        }
    }
}).$mount('#app');

