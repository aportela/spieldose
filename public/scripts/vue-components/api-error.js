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
                <h2>Uh oh! ...the server sent a <strong>invalid response</strong> ({{ apiResponse.status }} - {{ apiResponse.statusText }})</h2>
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
                        <h2>Api request url:</h2>
                        <pre>{{ apiRequest.url }}</pre>
                        <h2>Api request method:</h2>
                        <pre>{{ apiRequest.method  }}</pre>
                        <h2>Api request params:</h2>
                        <pre>{{ apiRequest.params }}</pre>
                    </div>
                    <div class="panel" v-if="activeTab == 'response'">
                        <h2>Api response headers:</h2>
                        <pre>{{ apiResponse.headers }}</pre>
                        <h2>Api response text:</h2>
                        <pre>{{ apiResponse.text }}</pre>
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
            activeTab: "request"
        });
    }, props: ['apiRequest', 'apiResponse'],
    methods: {
        toggleDetails() {
            this.visibleDetails = ! this.visibleDetails;
        },
        changeTab(tab) {
            this.activeTab = tab;
        }
    }
});
