"use strict";

/*
window.onhashchange = function(e) {
    console.log(e);
    switch(location.hash) {
        case "#/albums":
        break;
        case "#/artists":
        break;
        case "#/genres":
        break;
    }
};
*/

var httpRequest = function (method, url, data, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.onreadystatechange = function (e) {
        if (this.readyState == 4 && xhr.status !== 0) {
            var result = null;
            try {
                result = JSON.parse(xhr.responseText);
            } catch (e) {
                console.groupCollapsed("Error parsing JSON response");
                console.log(e);
                console.log(xhr.responseText);
                console.groupEnd();
            } finally {
                callback(xhr.status, result);
            }
        }
    }
    xhr.ontimeout = function (e) {
        callback(408, null);
    };
    xhr.send(data, null, 2);
}

var m = Vue.component('modal-component', {
    template: '#modal-template',
    data: function () {
        return ({
            visible: false,
            title: "Modal title",
            body: "Modal body"
        });
    },
    methods: {
        show: function () {
            this.visible = true;
        },
        hide: function () {
            this.visible = false;
        }
    }
});

var f = Vue.component('signin-component', {
    template: '#signin-template',
    ready: function () { },
    data: function () {
        return ({
            xhr: false,
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
            httpRequest("POST", "/api/user/signin.php", new FormData($("form#f_signin")[0]), function (httpStatusCode, response) {
                self.xhr = false;
                switch (httpStatusCode) {
                    case 404:
                        self.invalidUsername = true;
                        break;
                    case 200:
                        if (!response) {
                            alert("error");
                        } else {
                            if (!response.success) {
                                self.invalidPassword = true;
                            } else {
                                app.logged = true;
                            }
                        }
                        break;
                    default:
                        break;
                }
            });
        }
    }
});

var menu = Vue.component('spieldose-left-menu-sidebar-template-component', {
    template: '#spieldose-left-menu-sidebar-template',
    data: function () {
        return ({
            xhr: false,
            section: "#/dashboard"
        });
    },
    methods: {
        changeSection: function(s) {
            this.section = s;
            this.$parent.changeSection(s);
        },
        signout: function (e) {
            var self = this;
            self.xhr = true;
            httpRequest("POST", "/api/user/signout.php", new FormData(), function (httpStatusCode, response) {
                self.xhr = false;
                app.logged = false;
            });
        }
    }
});

var player = Vue.component('spieldose-right-player-sidebar-template-component', {
    template: '#spieldose-right-player-sidebar-template',
    data: function () {
        return ({
        });
    },
    methods: {
    }
});

var container = Vue.component('spieldose-component', {
    template: '#spieldose-template',
    data: function () {
        return ({
            xhr: false,
            section: "#/dashboard",
            artistList: []
        });
    },
    computed: {
    },
    methods: {
        changeSection: function(s) {
            this.section = s;
            var self = this;
            switch(s) {
                case "#/artists":
                    httpRequest("POST", "/api/artist/search.php", new FormData(), function (httpStatusCode, response) {
                        self.artistList = response.artists;
                    });
                break;
            }
        }
    }, filters: {
        encodeURI: function(str) {
            return(encodeURI(str));
        }
    }, components: {
        'spieldose-left-menu-sidebar-template-component': menu,
        'spieldose-right-player-sidebar-template-component': player
    }
});


var app = new Vue({
    el: "#app",
    data: {
        logged: true
    },
    components: {
        'modal-component': m,
        'signin-component': f,
        'spieldose-component': container
    }
});

