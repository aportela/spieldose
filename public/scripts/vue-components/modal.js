"use strict";

var vTemplateModal = function () {
    return `
        <div class="modal" v-bind:class="{ 'is-active': visible }">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ title }}</p>
                <button class="delete" v-on:click.prevent="hide"></button>
            </header>
            <section class="modal-card-body">
            <div class="content is-small">
                <pre>
                    {{ body }}
                </pre>
            </div>
            </section>
            <footer class="modal-card-foot">
                <a class="button" v-on:click.prevent="hide">Close</a>
            </footer>
        </div>
    </div>
    `;
}

/* modal component (warning & errors) */
var modal = Vue.component('spieldose-modal-component', {
    template: vTemplateModal(),
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
