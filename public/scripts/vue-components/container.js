var container = (function () {
    "use strict";

    var template = function () {
        return `
            <div>
                <section class="section is-fullheight is-light is-bold">
                    <div class="columns">
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

    var module = Vue.component('spieldose-app-component', {
        template: template(),
        methods: {
            signout: function (e) {
                bus.$emit("signOut");
            }, changeSection(routeName) {
                this.$router.push({ name: routeName });
            }
        }
    });

    return (module);
})();