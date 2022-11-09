const template = function () {
    return `
        <section class="panel" :class="extraClass">
            <p class="panel-heading">
                <span class="icon mr-1"><slot name="icon"></slot></span>
                <span><slot name="title"></slot></span>
                <span class="icon is-clickable is-pulled-right" v-bind:title="$t('commonMessages.refreshData')" v-on:click.prevent="onReload" v-else-if="reloadFunction">
                    <i class="fa-fw fas fa-cog fa-spin fa-fw" v-if="loading"></i>
                    <i class="fa-fw fas fa-exclamation-triangle" v-else-if="errors"></i>
                    <i class="fas fa-redo fa-fw" v-else></i>
                </span>
            </p>
            <slot name="body"></slot>
            <p class="panel-block title is-6 has-text-centered has-text-weight-bold is-block" v-if="errors">{{ $t("commonErrors.invalidAPIResponse") }}</p>
        </section>
    `;
};

export default {
    name: 'spieldose-dashboard-block',
    template: template(),
    props: [
        'loading',
        'errors',
        'extraClass',
        'reloadFunction'
    ],
    methods: {
        onReload: function () {
            if (!this.loading && this.reloadFunction && typeof this.reloadFunction === 'function') {
                this.reloadFunction();
            } else {
                return (false);
            }
        }
    }
}
