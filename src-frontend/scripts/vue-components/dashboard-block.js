const template = function () {
    return `
        <section class="panel" :class="extraClass">
            <p class="panel-heading">
                <span class="icon mr-1">
                    <i class="fa-fw fas fa-cog fa-spin fa-fw" v-if="loading"></i>
                    <i class="fa-fw fas fa-exclamation-triangle" v-else-if="errors"></i>
                    <i class="fa-fw fas fa-chart-line" v-else></i>
                </span>
                <span><slot name="title"></slot></span>
                <a class="icon is-pulled-right" v-bind:title="$t('commonMessages.refreshData')" v-on:click.prevent="loadChart();"><slot name="icon"></slot></a>
            </p>
            <slot name="body"></slot>
        </section>
    `;
};

export default {
    name: 'spieldose-dashboard-block',
    template: template(),
    data: function () {
        return ({
        });
    },
    props: [
        'loading',
        'errors',
        'extraClass',
    ]
}