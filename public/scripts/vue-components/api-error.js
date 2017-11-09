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
                <h2>Uh oh! ...the server sent a <strong>invalid response</strong> ({{ apiErrorResponse.code }})</h2>
                <p v-if="! visibleDetails"><a href="#" v-on:click.prevent="toggleDetails();">Follow</a> for  the rabbit.</p>
                <div v-if="visibleDetails">
                    <hr>
                    <h2>Api url:</h2>
                    <pre>{{ apiErrorResponse.url }}</pre>
                    <h2>Api method:</h2>
                    <pre>{{ apiErrorResponse.method }}</pre>
                    <h2>Api params:</h2>
                    <pre>{{ apiErrorResponse.params }}</pre>
                    <h2>Api response headers:</h2>
                    <pre>{{ apiErrorResponse.headers }}</pre>
                    <h2>Api response body:</h2>
                    <pre>{{ apiErrorResponse.body }}</pre>
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
            visibleDetails: false
        });
    }, props: ['apiErrorResponse'],
    methods: {
        toggleDetails() {
            this.visibleDetails = ! this.visibleDetails;
        }
    }
});
