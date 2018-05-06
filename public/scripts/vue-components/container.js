let container = (function () {
    "use strict";

    const template = function () {
        return `
            <div>
                <section class="section is-fullheight is-light is-bold">
                    <div class="columns is-desktop">
                        <!-- TODO: test class "is-hidden-touch" for mobile -->
                        <div class="is-narrow column">
                            <spieldose-player-component></spieldose-player-component>
                            <spieldose-menu-component></spieldose-menu-component>
                        </div>
                        <div class="column">
                            <router-view></router-view>
                        </div>
                    </div>
                </section>
                <player-navbar></player-navbar>
            </div>
        `;
    };

    /* main app container component */
    let module = Vue.component('spieldose-app-component', {
        template: template()
    });

    return (module);
})();