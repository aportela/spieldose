"use strict";

var vTemplateSignIn = function () {
    return `
    <!-- template credits: daniel (https://github.com/dansup) -->
    <section class="hero is-fullheight is-light is-bold">
        <div class="hero-body">
            <div class="container">
                <div class="columns is-vcentered">
                    <div class="column is-4 is-offset-4">
                        <div class="columns is-centered">
                            <!-- Apple Music Sound Equalizer in SVG by Geoff Graham (https://codepen.io/geoffgraham/pen/XmMJqj) -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="equilizer" viewBox="0 0 128 128">
                                <g>
                                    <title>Audio Equalizer</title>
                                    <rect class="bar" transform="translate(0,0)" y="15"></rect>
                                    <rect class="bar" transform="translate(25,0)" y="15"></rect>
                                    <rect class="bar" transform="translate(50,0)" y="15"></rect>
                                    <rect class="bar" transform="translate(75,0)" y="15"></rect>
                                    <rect class="bar" transform="translate(100,0)" y="15"></rect>
                                </g>
                            </svg>
                        </div>
                        <h1 class="title has-text-centered"><span class="icon is-medium"><i class="fa fa-music rainbow-transition" aria-hidden="true"></i></span> Spieldose <span class="icon is-medium"><i class="fa fa-music rainbow-transition" aria-hidden="true"></i></span></h1>
                        <h2 class="subtitle is-6 has-text-centered"><cite>...music for the Masses</cite></h2>
                        <form v-on:submit.prevent="submit">
                            <div class="box">
                                <label class="label">Email</label>
                                <p class="control" id="login-container" v-bind:class="{ 'has-icons-right' : invalidUsername }">
                                    <input class="input" type="email" name="email" maxlength="255" required autofocus v-bind:class="{ 'is-danger': invalidUsername }" v-bind:disabled="loading ? true: false" v-model="email">
                                    <span class="icon is-small is-right" v-show="invalidUsername"><i class="fa fa-warning"></i></span>
                                    <p class="help is-danger" v-show="invalidUsername">Email not found</p>
                                </p>
                                <label class="label">Password</label>
                                <p class="control" id="password-container" v-bind:class="{ 'has-icons-right' : invalidPassword }">
                                    <input class="input" type="password" name="password" required v-bind:class="{ 'is-danger': invalidPassword }" v-bind:disabled="loading ? true: false" v-model="password">
                                    <span class="icon is-small is-right" v-show="invalidPassword"><i class="fa fa-warning"></i></span>
                                    <p class="help is-danger" v-show="invalidPassword">Invalid password</p>
                                </p>
                                <hr>
                                <p class="control">
                                    <button type="submit" class="button is-primary" v-bind:class="{ 'is-loading': loading }" v-bind:disabled="loading ? true: false">Sign in</button>
                                    <button v-if="allowSignUp" type="button" class="button is-info" v-bind:class="{ 'is-loading': loading }" v-bind:disabled="loading ? true: false">Sign up</button>
                                </p>
                            </div>
                        </form>
                        <p class="has-text-centered">
                            <a href="https://github.com/aportela/spieldose"><span class="icon is-small"><i class="fa fa-github"></i></span>Project page</a> | <a href="mailto:766f6964+github@gmail.com">by alex</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <footer class="footer" v-if="errors">
            <spieldose-api-error-component v-bind:visible="errors" v-bind:apiError="apiError"></spieldose-api-error-component>
        </footer>
    </section>
    `;
}

/* signIn component */
var signIn = Vue.component('spieldose-signin-component', {
    template: vTemplateSignIn(),
    created: function () {
    },
    data: function () {
        return ({
            loading: false,
            email: "foo@bar",
            password: "secret",
            invalidUsername: false,
            invalidPassword: false,
            allowSignUp: false,
            errors: false,
            apiError: null,
        });
    },
    methods: {
        submit: function (e) {
            e.preventDefault();
            var self = this;
            self.invalidUsername = false;
            self.invalidPassword = false;
            self.loading = true;
            self.errors = false;
            var d = {
                email: this.email,
                password: this.password
            };
            spieldoseAPI.signIn(this.email, this.password, function (response) {
                if (response.ok) {
                    self.$router.push({ name: 'dashboard' });
                } else {
                    switch (response.status) {
                        case 404:
                            self.invalidUsername = true;
                            break;
                        case 401:
                            self.invalidPassword = true;
                            break;
                        default:
                            self.apiError = response.getApiErrorData();
                            self.errors = true;
                            break;
                    }
                    self.loading = false;
                }
            });
        }
    }
});