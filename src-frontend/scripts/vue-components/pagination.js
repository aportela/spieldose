const template = function () {
    return `
        <div>
            <nav class="pagination is-centered is-small" role="navigation" aria-label="pagination" v-if="visible">
                <button type="button" class="button is-small is-pink pagination-previous" :disabled="disabled || data.currentPage == 1" v-on:click.prevent="previous();">
                    <span class="icon is-small"><i class="fas fa-caret-left" aria-hidden="true"></i></span>
                    <span>{{ $t("pagination.buttons.previousPage") }}</span>
                </button>
                <button type="button" class="button is-small is-pink  pagination-next" :disabled="disabled || data.currentPage >= data.totalPages" v-on:click.prevent="next();">
                    <span>{{ $t("pagination.buttons.nextPage") }}</span>
                    <span class="icon is-small"><i class="fas fa-caret-right" aria-hidden="true"></i></span>
                </button>
                <ul class="pagination-list">
                    <!-- vuejs pagination inspired by Jeff (https://stackoverflow.com/a/35706926) -->
                    <li v-for="pageNumber in data.totalPages" v-if="true || showIntermediatePage(pageNumber)">
                        <button type="button" class="pagination-link" :class="{ 'is-current': data.currentPage == pageNumber }" :disabled="disabled" v-on:click.prevent="navigateTo(pageNumber);">{{ pageNumber }}</button>
                    </li>
                </ul>
            </nav>
            <div class="notification is-warning" v-if="invalidPage">{{ $t("pagination.labels.invalidPageOrNoResults") }}</div>
            <div class="is-clearfix" v-else></div>
        </div>
    `;
};

export default {
    name: 'spieldose-pagination',
    template: template(),
    props: [
        'data', 'disabled'
    ],
    computed: {
        visible: function () {
            return (this.data && this.data.totalPages > 1);
        },
        invalidPage: function () {
            return (this.data.totalPages > 0 && (this.data.currentPage < 1 || this.data.currentPage > this.data.totalPages));
        }
    },
    methods: {
        previous: function () {
            if (!this.disabled) {
                if (this.data.currentPage > 1) {
                    this.data.currentPage--;
                    this.$emit('pagination-changed', this.data.currentPage);
                }
            }
        },
        next: function () {
            if (!this.disabled) {
                if (this.data.currentPage < this.data.totalPages) {
                    this.data.currentPage++;
                    this.$emit('pagination-changed', this.data.currentPage);
                }
            }
        },
        navigateTo: function (pageIdx) {
            if (!this.disabled) {
                if (pageIdx > 0 && pageIdx <= this.data.totalPages) {
                    this.data.currentPage = pageIdx;
                    this.$emit('pagination-changed', this.data.currentPage);
                }
            }
        },
        showIntermediatePage: function (pageNumber) {
            return (pageNumber < 3 || Math.abs(pageNumber - this.data.currentPage) < 3 || this.data.totalPages - 2 < pageNumber);
        }
    }
}