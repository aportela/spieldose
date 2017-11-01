"use strict";

var vTemplateBrowseGenres = function () {
    return `
    <section v-show="section == '#/genres'"class="section" id="section-genres">
    </section>
    `;
}

var browseGenres = Vue.component('spieldose-browse-genres', {
    template: vTemplateBrowseGenres()
    , props: ['section'
    ]
});

