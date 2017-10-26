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

var app = new Vue({
    el: "#app",
    data: function () {
        return ({
            xhr: false
        });
    },
    methods: {
        signout: function(e) {
            e.preventDefault();
            var self = this;
            self.xhr = true;
            var f = $("form#frm_signout");
            jsonHttpRequest($(f).attr("method"), $(f).attr("action"), {}, function (httpStatusCode, response, originalResponse) {
                self.xhr = false;
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
