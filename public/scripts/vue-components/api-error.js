"use strict";

var vTemplateApiError = function () {
    return `
    <article class="message is-danger">
        <div class="message-header">
        <p><i class="fa fa-bomb" aria-hidden="true"></i> Error</p>
        </div>
        <div class="message-body">
            <div class="content">
                <h1 class="has-text-centered">“I'm sorry Dave. I'm afraid I can't do that”</h1>
                <h2>Uh oh! ...the server sent a <strong>invalid response</strong> ({{ apiError.response.status }} - {{ apiError.response.statusText }})</h2>
                <p v-if="! visibleDetails"><a href="#" v-on:click.prevent="toggleDetails();">Follow</a> for  the rabbit.</p>
                <div v-if="visibleDetails">
                    <hr>
                    <div class="tabs is-medium is-toggle">
                        <ul>
                            <li class="is-marginless" v-bind:class="{ 'is-active' : activeTab == 'request' }"><a class="no-text-decoration" href="#" v-on:click.prevent="changeTab('request');"><span class="icon is-small"><i class="fa fa-upload"></i></span><span>Request</span></a></li>
                            <li class="is-marginless" v-bind:class="{ 'is-active' : activeTab == 'response' }"><a class="no-text-decoration"href="#" v-on:click.prevent="changeTab('response');"><span class="icon is-small"><i class="fa fa-download"></i></span><span>Response</span></a></li>
                        </ul>
                    </div>
                    <div class="panel" v-if="activeTab == 'request'">
                        <h2>Api request method & url:</h2>
                        <pre>{{ apiError.request.method }} {{ apiError.request.url }}</pre>
                        <div class="tabs is-small is-toggle">
                            <ul>
                                <li class="is-marginless" v-bind:class="{ 'is-active' : activeRequestTab == 'body' }"><a class="no-text-decoration" href="#" v-on:click.prevent="changeRequestTab('body');"><span class="icon is-small"><i class="fa fa-file-text-o"></i></span><span>Body</span></a></li>
                                <li class="is-marginless" v-bind:class="{ 'is-active' : activeRequestTab == 'headers' }"><a class="no-text-decoration"href="#" v-on:click.prevent="changeRequestTab('headers');"><span class="icon is-small"><i class="fa fa-list"></i></span><span>Headers</span></a></li>
                            </ul>
                        </div>
                        <div v-if="activeRequestTab == 'body'">
                            <h2>Api request body:</h2>
                            <pre>{{ apiError.request.body }}</pre>
                        </div>
                        <div v-if="activeRequestTab == 'headers'">
                            <h2>Api request headers:</h2>
                            <pre>{{ apiError.request.headers }}</pre>
                        </div>
                    </div>
                    <div class="panel" v-if="activeTab == 'response'">
                        <div class="tabs is-small is-toggle">
                            <ul>
                                <li class="is-marginless" v-bind:class="{ 'is-active' : activeResponseTab == 'text' }"><a class="no-text-decoration" href="#" v-on:click.prevent="changeResponseTab('text');"><span class="icon is-small"><i class="fa fa-file-text-o"></i></span><span>Body</span></a></li>
                                <li class="is-marginless" v-bind:class="{ 'is-active' : activeResponseTab == 'headers' }"><a class="no-text-decoration"href="#" v-on:click.prevent="changeResponseTab('headers');"><span class="icon is-small"><i class="fa fa-list"></i></span><span>Headers</span></a></li>
                            </ul>
                        </div>
                        <div v-if="activeResponseTab == 'text'">
                            <h2>Api response text:</h2>
                            <pre>{{ apiError.response.text }}</pre>
                        </div>
                        <div v-if="activeResponseTab == 'headers'">
                            <h2>Api response headers:</h2>
                            <pre>{{ apiError.response.headers }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </article>
    `;
}

/* app (logged) menu component */
var apiError = Vue.component('spieldose-api-error-component', {
    template: vTemplateApiError(),
    data: function () {
        return ({
            visibleDetails: false,
            activeTab: "request",
            activeRequestTab: "body",
            activeResponseTab: "text"
        });
    }, props: ['apiError'],
    methods: {
        toggleDetails() {
            this.visibleDetails = !this.visibleDetails;
        },
        changeTab(tab) {
            this.activeTab = tab;
        },
        changeRequestTab(tab) {
            this.activeRequestTab = tab;
        },
        changeResponseTab(tab) {
            this.activeResponseTab = tab;
        }
    }
});
