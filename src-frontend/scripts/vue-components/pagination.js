const template = function () {
    return `
        <div>
            <div class="columns">
                <div class="column has-text-left">
                    <button type="button" class="button is-dark pagination-previous" :disabled="loading" @click.prevent="previous();">
                        <span class="icon is-small"><i class="fas fa-caret-left" aria-hidden="true"></i></span>
                        <span>{{ $t("pagination.buttons.previousPage") }}</span>
                    </button>
                </div>
                <div class="column has-text-centered">
                    <div class="field is-grouped">
                        <p class="control" v-for="pageNumber in data.totalPages" v-show="showIntermediatePage(pageNumber)">
                            <button class="button is-outline-dark"  :class="{'is-dark': isCurrentPage(pageNumber) }" :disabled="loading" @click.prevent="navigateTo(pageNumber);">{{ pageNumber }}</button>
                        </p>
                    </div>
                </div>
                <div class="column has-text-right">
                    <button type="button" class="button is-dark pagination-next" :disabled="loading" @click.prevent="next();">
                        <span>{{ $t("pagination.buttons.nextPage") }}</span>
                        <span class="icon is-small"><i class="fas fa-caret-right" aria-hidden="true"></i></span>
                    </button>
                </div>
            </div>
            <div class="is-clearfix"></div>
        </div>
    `;
};

export default {
    name: 'spieldose-pagination',
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
                    this.$emit('pagination-changed', this.data.actualPage);
                }
            }
        },
        next: function () {
            if (!this.loading) {
                if (this.data.actualPage < this.data.totalPages) {
                    this.data.actualPage++;
                    this.$emit('pagination-changed', this.data.actualPage);
                }
            }
        },
        navigateTo: function (pageIdx) {
            if (!this.loading) {
                if (pageIdx > 0 && pageIdx <= this.data.totalPages) {
                    this.data.actualPage = pageIdx;
                    this.$emit('pagination-changed', this.data.actualPage);
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
}