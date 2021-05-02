const template = function () {
    return `
        <div>
            <nav class="pagination is-centered level" v-if="visible">
                <a class="button is-link pagination-previous" :class="{ 'disabled': loading }" @click.prevent="previous();">
                    <span class="icon is-small"><i class="fas fa-caret-left" aria-hidden="true"></i></span>
                    <span>{{ $t("pagination.buttons.previousPage") }}</span>
                    </a>
                <a class="button is-link pagination-next" :class="{ 'disabled': loading }" @click.prevent="next();">
                    <span>{{ $t("pagination.buttons.nextPage") }}</span>
                    <span class="icon is-small"><i class="fas fa-caret-right" aria-hidden="true"></i></span>
                </a>
                <ul class="pagination-list">
                    <!-- vuejs pagination inspired by Jeff (https://stackoverflow.com/a/35706926) -->
                    <li v-for="pageNumber in data.totalPages">
                        <a class="pagination-link" v-if="showIntermediatePage(pageNumber)" :class="{'is-current': isCurrentPage(pageNumber), 'disabled': loading }" @click.prevent="navigateTo(pageNumber);">{{ pageNumber }}</a>
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
    created: function() {
        console.log(this.loading);
    },
    watch: {
        data: function(newValue) {
            console.log(newValue);
        },
        loading: function(newValue) {
            console.log(newValue);
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