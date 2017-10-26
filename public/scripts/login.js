"use strict";

/* global object for events between vuejs components */
const bus = new Vue();

/* modal component (warning & errors) */
var modal = Vue.component('spieldose-modal-component', {
    template: '#modal-template',
    data: function () {
        return ({
            visible: false,
            title: "Default modal title",
            body: "Default modal body"
        });
    },
    created: function () {
        var self = this;
        bus.$on("showModal", function (title, body) {
            self.show(title, body);
        });
    },
    methods: {
        show: function (title, body) {
            this.title = title;
            this.body = body;
            this.visible = true;
        },
        hide: function () {
            this.visible = false;
            this.$emit("closeModal");
        }
    }
});

/* signIn component */
var signIn = Vue.component('spieldose-signin-component', {
    template: '#signin-template',
    created: function () {
    },
    data: function () {
        return ({
            xhr: false,
            email: "foo@bar",
            password: "secret",
            invalidUsername: false,
            invalidPassword: false
        });
    },
    methods: {
        submit: function (e) {
            var self = this;
            self.invalidUsername = false;
            self.invalidPassword = false;
            self.xhr = true;
            var f = $("form#f_signin");
            var d = {
                email: this.email,
                password: this.password
            };
            jsonHttpRequest($(f).attr("method"), $(f).attr("action"), d, function (httpStatusCode, response, originalResponse) {
                self.xhr = false;
                switch (httpStatusCode) {
                    case 404:
                        self.invalidUsername = true;
                        break;
                    case 401:
                        self.invalidPassword = true;
                    break;
                    case 200:
                            window.location.href = "/app";
                    break;
                    default:
                        bus.$emit("showModal", "Error", "Invalid server response: " + httpStatusCode + "\n" + originalResponse);
                        break;
                }
            });
        }
    }
});

var app = new Vue({
    el: "#app"
});
