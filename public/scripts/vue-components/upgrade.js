var upgrade = (function () {
    "use strict";

    var template = function () {
        return `
    <!-- template credits: daniel (https://github.com/dansup) -->
    <section class="hero is-fullheight is-light is-bold">
        <div class="hero-body">
            <div class="container">
                <div class="columns is-vcentered">
                    <div class="column is-4 is-offset-4">
                        <h1 class="title has-text-centered"><span class="icon is-medium"><i class="fa fa-music rainbow-transition" aria-hidden="true"></i></span> Spieldose <span class="icon is-medium"><i class="fa fa-music rainbow-transition" aria-hidden="true"></i></span></h1>
                        <h2 class="subtitle is-6 has-text-centered"><cite>...music for the Masses</cite></h2>

                        <div class="notification is-warning" v-if="upgradeAvailable">
                            <p class="title is-5"><span class="icon"><i class="fa fa-warning"></i></span> New database version available</p>
                            <hr>
                            <p class="subtitle is-5">An upgrade is required</p>
                            <p>Execute this commandline:</p>
                            <p>php tools/install-upgrade-db.php</p>
                        </div>
                        <div class="notification is-success" v-else>
                            <p class="title is-5"><span class="icon"><i class="fa fa-ok"></i></span> Your system is up to date</p>
                        </div>

                        <p class="has-text-centered">
                            <a href="https://github.com/aportela/spieldose"><span class="icon is-small"><i class="fa fa-github"></i></span>Project page</a> | <a href="mailto:766f6964+github@gmail.com">by alex</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    `;
    };

    /* signIn component */
    var module = Vue.component('spieldose-upgrade-component', {
        template: template(),
        data: function () {
            return ({
            });
        }, computed: {
            upgradeAvailable: function() {
                return(initialState.upgradeAvailable);
            }
        }
    });

    return (module);
})();