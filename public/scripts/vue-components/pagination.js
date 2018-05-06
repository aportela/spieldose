let pagination = (function () {
    "use strict";

    const template = function () {
        return `
            <div>
                <nav class="pagination is-centered level" v-if="visible">
                    <a class="button is-link pagination-previous" v-bind:disabled="loading" v-on:click.prevent="previous();">
                        <span class="icon is-small"><i class="fas fa-caret-left" aria-hidden="true"></i></span>
                        <span>Previous</span>
                        </a>
                    <a class="button is-link pagination-next" v-bind:disabled="loading" v-on:click.prevent="next();">
                        <span>Next</span>
                        <span class="icon is-small"><i class="fas fa-caret-right" aria-hidden="true"></i></span>
                    </a>
                    <ul class="pagination-list">
                        <!-- vuejs pagination inspired by Jeff (https://stackoverflow.com/a/35706926) -->
                        <li v-for="pageNumber in data.totalPages" v-if="showIntermediatePage(pageNumber)">
                            <a class="pagination-link" :class="{'is-current': isCurrentPage(pageNumber) }" v-bind:disabled="loading" v-on:click.prevent="navigateTo(pageNumber);">{{ pageNumber }}</a>
                        </li>
                    </ul>
                </nav>
                <div class="notification is-warning" v-if="invalidPage">The specified page is incorrect or there are no results to display</div>
                <div class="is-clearfix" v-else></div>
            </div>
        `;
    };

    let module = Vue.component('spieldose-pagination', {
        template: template(),
        props: [
            'data', 'loading'
        ],
        computed: {
            visible: function () {
                return (this.data && this.data.totalPages > 1);
            },
            invalidPage: function () {
                return (this.data.totalPages > 0 && (this.data.actualPage < 1 || this.data.actualPage > this.data.totalPages));
            }
        },
        methods: {
            previous: function () {
                if (!this.loading) {
                    if (this.data.actualPage > 1) {
                        this.data.actualPage--;
                        this.$emit("pagination-changed", this.data.actualPage);
                    }
                }
            },
            next: function () {
                if (!this.loading) {
                    if (this.data.actualPage < this.data.totalPages) {
                        this.data.actualPage++;
                        this.$emit("pagination-changed", this.data.actualPage);
                    }
                }
            },
            navigateTo: function (pageIdx) {
                if (!this.loading) {
                    if (pageIdx > 0 && pageIdx <= this.data.totalPages) {
                        this.data.actualPage = pageIdx;
                        this.$emit("pagination-changed", this.data.actualPage);
                    }
                }
            },
            showIntermediatePage: function (pageNumber) {
                return (pageNumber < 3 || Math.abs(pageNumber - this.data.actualPage) < 3 || this.data.totalPages - 2 < pageNumber);
            },
            isCurrentPage: function (pageNumber) {
                return (this.data.actualPage === pageNumber);
            }
        }
    });

    return (module);
})();