"use strict";

const DEFAULT_SECTION_RESULTS_PAGE = 32;

/* global object for events between vuejs components */
const bus = new Vue();

/* change section event */
window.onhashchange = function (e) {
    bus.$emit("hashChanged", location.hash);
};


/* app chart (test) component */
var chart2 = Vue.component('spieldose-chart-recent', {
    template: '#chart-template-recent',
    data: function () {
        return ({
            xhr: false,
            iconClass: 'fa-pie-chart',
            items: [],
            entity: 0
        });
    },
    created: function () {
        this.loadChartData();
    }, methods: {
        loadChartData: function () {
            var self = this;
            self.items = [];
            var url = null;
            if (this.type == "recentlyAdded") {
                url = '/api/metrics/recently_added';
            } else if (this.type == "recentlyPlayed") {
                url = '/api/metrics/recently_played';
            }
            var d = {};
            switch (this.entity) {
                case 0: // tracks
                    d.entity = "tracks";
                    break;
                case 1: // artists
                    d.entity = "artists";
                    break;
                case 2: // albums
                    d.entity = "albums";
                    break;
            }
            self.xhr = true;
            jsonHttpRequest("POST", url, d, function (httpStatusCode, response) {
                self.xhr = false;
                self.items = response.metrics;
            });
        }, changeEntity: function (e) {
            this.entity = e;
            this.loadChartData();
        }
    },
    props: ['type', 'title']
});

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
