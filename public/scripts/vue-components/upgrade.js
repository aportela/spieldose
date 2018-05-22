let upgrade = (function () {
    "use strict";

    const template = function () {
        return `
            <!-- template credits: daniel (https://github.com/dansup) -->
            <section class="hero is-fullheight is-light is-bold">
                <div class="hero-body">
                    <div class="container">
                        <div class="columns is-vcentered">
                            <div class="column is-4 is-offset-4">
                                <h1 class="title has-text-centered"><span class="icon is-medium"><i class="fas fa-music" aria-hidden="true"></i></span> Spieldose <span class="icon is-medium"><i class="fas fa-music" aria-hidden="true"></i></span></h1>
                                <h2 class="subtitle is-6 has-text-centered"><cite>{{ $t("commonLabels.slogan") }}</cite></h2>
                                <div class="notification is-warning" v-if="upgradeAvailable">
                                    <p class="title is-5"><span class="icon"><i class="fa fa-exclamation-triangle"></i></span> {{ $t("upgrade.labels.newDatabaseVersionAvailable") }}</p>
                                    <hr>
                                    <p class="subtitle is-5">{{ $t("upgrade.labels.upgradeRequired") }}</p>
                                    <p>{{ $t("upgrade.labels.executeThisCommandline") }}</p>
                                    <p>php tools/install-upgrade-db.php</p>
                                </div>
                                <div class="notification is-success" v-else>
                                    <p class="title is-5"><span class="icon"><i class="fas fa-check"></i></span> {{ $t("upgrade.labels.updateNotRequired") }}</p>
                                </div>
                                <p class="has-text-centered">
                                <a href="https://github.com/aportela/spieldose" target="_blank"><span class="icon is-small"><i class="fab fa-github"></i></span>{{ $t("commonLabels.projectPageLinkLabel") }}</a> | <a href="https://github.com/aportela" target="_blank">{{ $t("commonLabels.authorLinkLabel") }}</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        `;
    };

    /* signIn component */
    let module = Vue.component('spieldose-upgrade-component', {
        template: template()
        , computed: {
            upgradeAvailable: function () {
                return (initialState.upgradeAvailable);
            }
        }
    });

    return (module);
})();