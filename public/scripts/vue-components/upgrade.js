const template=function(){return'\n        \x3c!-- template credits: daniel (https://github.com/dansup) --\x3e\n        <section class="hero is-fullheight is-light is-bold">\n            <div class="hero-body">\n                <div class="container">\n                    <div class="columns is-vcentered">\n                        <div class="column is-4 is-offset-4">\n                            <h1 class="title has-text-centered"><span class="icon is-medium"><i class="fas fa-music" aria-hidden="true"></i></span> Spieldose <span class="icon is-medium"><i class="fas fa-music" aria-hidden="true"></i></span></h1>\n                            <h2 class="subtitle is-6 has-text-centered"><cite>{{ $t("commonLabels.slogan") }}</cite></h2>\n                            <div class="notification is-warning" v-if="upgradeAvailable">\n                                <p class="title is-5"><span class="icon"><i class="fa fa-exclamation-triangle"></i></span> {{ $t("upgrade.labels.newDatabaseVersionAvailable") }}</p>\n                                <hr>\n                                <p class="subtitle is-5">{{ $t("upgrade.labels.upgradeRequired") }}</p>\n                                <p>{{ $t("upgrade.labels.executeThisCommandline") }}</p>\n                                <p>php tools/install-upgrade-db.php</p>\n                            </div>\n                            <div class="notification is-success" v-else>\n                                <p class="title is-5"><span class="icon"><i class="fas fa-check"></i></span> {{ $t("upgrade.labels.updateNotRequired") }}</p>\n                            </div>\n                            <p class="has-text-centered">\n                            <a href="https://github.com/aportela/spieldose" target="_blank"><span class="icon is-small"><i class="fab fa-github"></i></span>{{ $t("commonLabels.projectPageLinkLabel") }}</a> | <a href="https://github.com/aportela" target="_blank">{{ $t("commonLabels.authorLinkLabel") }}</a>\n                            </p>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </section>\n    '};export default{name:"spieldose-upgrade-component",template:'\n        \x3c!-- template credits: daniel (https://github.com/dansup) --\x3e\n        <section class="hero is-fullheight is-light is-bold">\n            <div class="hero-body">\n                <div class="container">\n                    <div class="columns is-vcentered">\n                        <div class="column is-4 is-offset-4">\n                            <h1 class="title has-text-centered"><span class="icon is-medium"><i class="fas fa-music" aria-hidden="true"></i></span> Spieldose <span class="icon is-medium"><i class="fas fa-music" aria-hidden="true"></i></span></h1>\n                            <h2 class="subtitle is-6 has-text-centered"><cite>{{ $t("commonLabels.slogan") }}</cite></h2>\n                            <div class="notification is-warning" v-if="upgradeAvailable">\n                                <p class="title is-5"><span class="icon"><i class="fa fa-exclamation-triangle"></i></span> {{ $t("upgrade.labels.newDatabaseVersionAvailable") }}</p>\n                                <hr>\n                                <p class="subtitle is-5">{{ $t("upgrade.labels.upgradeRequired") }}</p>\n                                <p>{{ $t("upgrade.labels.executeThisCommandline") }}</p>\n                                <p>php tools/install-upgrade-db.php</p>\n                            </div>\n                            <div class="notification is-success" v-else>\n                                <p class="title is-5"><span class="icon"><i class="fas fa-check"></i></span> {{ $t("upgrade.labels.updateNotRequired") }}</p>\n                            </div>\n                            <p class="has-text-centered">\n                            <a href="https://github.com/aportela/spieldose" target="_blank"><span class="icon is-small"><i class="fab fa-github"></i></span>{{ $t("commonLabels.projectPageLinkLabel") }}</a> | <a href="https://github.com/aportela" target="_blank">{{ $t("commonLabels.authorLinkLabel") }}</a>\n                            </p>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </section>\n    ',computed:{upgradeAvailable:function(){return initialState.upgradeAvailable}}};