"use strict";

var vTemplateDebug = function () {
    return `
        <div id="debug" class="notification is-warning" v-show="visible">
            <button class="delete" v-on:click="close();"></button>
            {{ text }}
        </div>
    `;
}

/* modal component (warning & errors) */
var debug = Vue.component('spieldose-debug', {
    template: vTemplateDebug(),
    data: function () {
        return ({
            visible: false,
            text: null,
            timeout: null
        });
    },
    created: function () {
        var self = this;
        bus.$on("debug", function (message, obj = null) {
            self.text = message;
            self.visible = true;
            self.timeout = setTimeout(function () {
                self.visible = false;
                self.timeout = null;
            }, 500);
        });
    },
    methods: {
        close: function() {
            this.visible = false;
        }
    }
});
