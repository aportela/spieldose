var browsePaths = (function () {
    "use strict";

    var template = function () {
        return `
    <div class="container is-fluid box">
        <p class="title is-1 has-text-centered">Browse paths</i></p>
        <div v-if="! errors">
            <div class="field">
                <div class="control has-icons-left" v-bind:class="loading ? 'is-loading': ''">
                    <input class="input" :disabled="loading" v-focus v-model.trim="nameFilter" type="text" placeholder="search path name..." v-on:keyup.esc="abortInstantSearch();" v-on:keyup="instantSearch();">
                    <span class="icon is-small is-left">
                        <i class="fa fa-search"></i>
                    </span>
                </div>
            </div>
            <nav class="breadcrumb has-arrow-separator" aria-label="breadcrumbs">
                <ul>
                    <li><a href="#"><span>Current path:</span></a></li>
                    <li><a href="#" v-on:click.prevent="search('');">Root</a></li>
                    <li v-if="currentPath"><a href="#">{{ currentPath }}</a></li>
                </ul>
            </nav>
            <div class="path-item box has-text-centered" v-for="item in currentPathFolders" v-show="! loading">
                <p class="path-item-icon" v-on:click.prevent="search(item.path);">
                    <span class="icon has-text-light">
                        <i class="fa fa-folder fa-5x"></i>
                    </span>
                </p>
                <p class="path-info">
                    <strong>“{{ item.path }}”</strong>
                </p>
                <p class="content is-small">{{ item.totalTracks }} tracks</p>
                <div class="field has-addons">
                    <p class="control">
                        <a class="button is-small is-link" v-on:click.prevent="play(item.path);">
                            <span class="icon is-small"><i class="fa fa-play"></i></span>
                            <span>play</span>
                        </a>
                    </p>
                    <p class="control">
                        <a class="button is-small is-info" v-on:click.prevent="enqueue(item.path);">
                            <span class="icon is-small"><i class="fa fa-plus-square"></i></span>
                            <span>enqueue</span>
                        </a>
                    </p>
                </div>
            </div>
            <div class="is-clearfix"></div>
        </div>
        <spieldose-api-error-component v-else v-bind:apiError="apiError"></spieldose-api-error-component>
    </div>
    `;
    };

    var module = Vue.component('spieldose-browse-paths', {
        template: template(),
        data: function () {
            return ({
                loading: false,
                errors: false,
                apiError: null,
                nameFilter: null,
                timeout: null,
                currentPath: null,
                currentPathFolders: [],
                pager: getPager(),
                playerData: sharedPlayerData
            });
        }, directives: {
            focus: {
                update: function (el) {
                    el.focus();
                }
            }
        }, created: function () {
            this.search("");
        }, methods: {
            search: function (path) {
                var self = this;
                this.currentPath = path;
                spieldoseAPI.searchPaths(path, function (response) {
                    if (response.ok) {
                        self.currentPathFolders = response.body.paths;
                    } else {
                        self.errors = true;
                        self.apiError = response.getApiErrorData();
                        self.loading = false;
                    }
                });
            },
            play: function (path) {
                var self = this;
                spieldoseAPI.getPathTracks(path, function (response) {
                    if (response.ok) {
                        if (response.body.tracks && response.body.tracks.length > 0) {
                            self.playerData.replace(response.body.tracks);
                        }
                    } else {
                        self.errors = true;
                        self.apiError = response.getApiErrorData();
                    }
                });
            },
            enqueue: function (path) {
                var self = this;
                spieldoseAPI.getPathTracks(path, function (response) {
                    if (response.ok) {
                        if (response.body.tracks && response.body.tracks.length > 0) {
                            self.playerData.enqueue(response.body.tracks);
                        }
                    } else {
                        self.errors = true;
                        self.apiError = response.getApiErrorData();
                    }
                });
            }
        }
    });

    return (module);
})();