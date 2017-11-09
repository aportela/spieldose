"use strict";

var vTemplateApiError = function () {
    return `
    <article class="message is-danger">
        <div class="message-header">
        <p>Error</p>
        <button class="delete" aria-label="delete"></button>
        </div>
        <div class="message-body">
            <div class="content">
                <h1>The server sent a <strong>invalid response</strong> ({{ apiErrorResponse.code }})</h1>
                <h2>Api url:</strong> {{ apiErrorResponse.url }}</h2>
                <h2><strong>Api params:</strong></h2>
                <pre>{{ apiErrorResponse.params }}</pre>
                <h2><strong>Api response:</strong></h2>
                <pre>{{ apiErrorResponse.text }}</pre>
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
            details: null
        });
    }, props: ['apiErrorResponse']
});
