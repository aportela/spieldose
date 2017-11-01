"use strict";

var vTemplatePreferences = function () {
    return `
    <section v-show="section == '#/preferences'"class="section" id="section-preferences">
    </section>
    `;
}

var preferences = Vue.component('spieldose-preferences', {
    template: vTemplatePreferences()
    , props: ['section'
    ]
});
