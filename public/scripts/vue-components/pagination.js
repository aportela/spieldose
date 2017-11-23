var pagination = (function () {
    "use strict";

    var template = function () {
        return `
        <div>
            <nav class="pagination is-centered level" v-if="visible">
                <a :disabled=loading class="button is-link pagination-previous" v-on:click.prevent="previous"><span class="icon is-small"><i class="fa fa-caret-left" aria-hidden="true"></i></span>Previous</a>
                <a :disabled=loading class="button is-link pagination-next" v-on:click.prevent="next">Next<span class="icon is-small"><i class="fa fa-caret-right" aria-hidden="true"></i></span></a>
                <ul class="pagination-list">
                    <!-- vuejs pagination inspired by Jeff (https://stackoverflow.com/a/35706926) -->
                    <li v-for="pageNumber in data.totalPages" v-if="pageNumber < 3 || Math.abs(pageNumber - data.actualPage) < 3 || data.totalPages - 2 < pageNumber">
                        <a href="#" :disabled=loading v-on:click.prevent="navigateTo(pageNumber)" class="pagination-link" :class="{'is-current': data.actualPage === pageNumber}">{{ pageNumber }}</a>
                    </li>
                </ul>
            </nav>
            <div class="notification is-warning" v-if="invalidPage">The selected page is invalid or has no results</div>
            <div class="is-clearfix" v-else></div>
        </div>
    `;
    };

    var module = Vue.component('spieldose-pagination', {
        template: template(),
        props: ['data', 'loading'
        ], computed: {
            visible: function () {
                return (this.data && this.data.totalPages > 1);
            },
            invalidPage: function() {
                return(this.data.totalPages > 0 &&
                    (this.data.actualPage < 1 || this.data.actualPage > this.data.totalPages)
                );
            }
        }, methods: {
            previous: function () {
                if (this.data.actualPage > 1) {
                    this.data.actualPage--
                    this.data.refresh();
                }
            },
            next: function () {
                if (this.data.actualPage < this.data.totalPages) {
                    this.data.actualPage++
                    this.data.refresh();
                }
            },
            navigateTo: function (pageIdx) {
                if (pageIdx > 0 && pageIdx <= this.data.totalPages) {
                    this.data.actualPage = pageIdx;
                    this.data.refresh();
                }
            }
        }
    });

    return (module);
})();