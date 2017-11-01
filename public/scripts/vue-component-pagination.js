"use strict";

var vTemplatePagination = function () {
    return `
    <nav class="pagination is-centered level">
        <a class="button is-link pagination-previous" v-on:click.prevent="previous"><span class="icon is-small"><i class="fa fa-caret-left" aria-hidden="true"></i></span>Previous</a>
        <a class="button is-link pagination-next" v-on:click.prevent="next">Next<span class="icon is-small"><i class="fa fa-caret-right" aria-hidden="true"></i></span></a>
        <ul class="pagination-list">
            <!-- vuejs pagination inspired by Jeff (https://stackoverflow.com/a/35706926) -->
            <li v-for="pageNumber in totalPages" v-if="pageNumber < 3 || Math.abs(pageNumber - actualPage) < 3 || totalPages - 2 < pageNumber">
                <a href="#" v-on:click.prevent="navigateTo(pageNumber)" class="pagination-link" :class="{'is-current': actualPage === pageNumber}">{{ pageNumber }}</a>
            </li>
        </ul>
    </nav>
    `;
}

var pagination = Vue.component('spieldose-pagination', {
    template: vTemplatePagination(),
    data: function () {
        return ({
            actualPage: 1,
            totalResults: 0,
            resultsPage: DEFAULT_SECTION_RESULTS_PAGE,
            totalPages: 0
        });
    }, props: ['searchEvent'
    ], computed: {
        visible: function () {
            return (this.totalResults > 0 && this.totalPages > 0);
        }
    }, created: function () {
        var self = this;
        bus.$on("updatePager", function (actualPage, totalPages, totalResults) {
            self.actualPage = actualPage;
            self.totalPages = totalPages;
            self.totalResults = totalResults;
        });
    }, methods: {
        previous: function () {
            if (this.actualPage > 1) {
                bus.$emit(this.searchEvent, null, this.actualPage - 1, this.resultsPage);
            }
        },
        next: function () {
            if (this.actualPage < this.totalPages) {
                bus.$emit(this.searchEvent, null, this.actualPage + 1, this.resultsPage);
            }
        },
        navigateTo: function (pageIdx) {
            if (pageIdx > 0 && pageIdx <= this.totalPages) {
                bus.$emit(this.searchEvent, null, pageIdx, this.resultsPage);
            }
        }
    }
});
