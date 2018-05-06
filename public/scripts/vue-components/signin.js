let signInUp = (function () {
    "use strict";

    const template = function () {
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
                                <h1 class="title has-text-centered"><span class="icon is-medium"><i class="fas fa-music" aria-hidden="true"></i></span> Spieldose <span class="icon is-medium"><i class="fas fa-music" aria-hidden="true"></i></span></h1>
                                <h2 class="subtitle is-6 has-text-centered"><cite>...music for the Masses</cite></h2>
                                <div class="tabs is-toggle is-radiusless spieldose-tabs-without-margin-bottom" v-if="allowSignUp">
                                    <ul>
                                        <li v-bind:class="{ 'is-active': isSignInTabActive }">
                                            <a v-on:click.prevent="changeTab('signin');">
                                                <span class="icon is-small"><i class="fas fa-user"></i></span>
                                                <span>Sign in</span>
                                            </a>
                                        </li>
                                        <li v-bind:class="{ 'is-active': isSignUpTabActive }">
                                            <a v-on:click.prevent="changeTab('signup');">
                                                <span class="icon is-small"><i class="fas fa-user-plus"></i></span>
                                                <span>Sign up</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <form v-on:submit.prevent="submitSignIn" v-show="isSignInTabActive">
                                    <div class="box is-radiusless">
                                        <label class="label">Email</label>
                                        <p class="control has-icons-left" v-bind:class="{ 'has-icons-right' : validator.hasInvalidField('signInEmail') }">
                                            <input class="input" type="email" ref="signInEmail" maxlength="255" required v-bind:class="{ 'is-danger': validator.hasInvalidField('signInEmail') }" v-bind:disabled="loading ? true: false" v-model="signInEmail">
                                            <span class="icon is-small is-left"><i class="fas fa-envelope"></i></span>
                                            <span class="icon is-small is-right" v-show="validator.hasInvalidField('signInEmail')"><i class="fas fa-warning"></i></span>
                                            <p class="help is-danger" v-show="validator.hasInvalidField('signInEmail')">{{ validator.getInvalidFieldMessage('signInEmail') }}</p>
                                        </p>
                                        <label class="label">Password</label>
                                        <p class="control has-icons-left" v-bind:class="{ 'has-icons-right' : validator.hasInvalidField('signInPassword') }">
                                            <input class="input" type="password" required v-bind:class="{ 'is-danger': validator.hasInvalidField('signInPassword') }" v-bind:disabled="loading ? true: false" v-model="signInPassword">
                                            <span class="icon is-small is-left"><i class="fas fa-key"></i></span>
                                            <span class="icon is-small is-right" v-show="validator.hasInvalidField('signInPassword')"><i class="fas fa-warning"></i></span>
                                            <p class="help is-danger" v-show="validator.hasInvalidField('signInPassword')">{{ validator.getInvalidFieldMessage('signInPassword') }}</p>
                                        </p>
                                        <hr>
                                        <p class="control">
                                            <button type="submit" class="button is-black" v-bind:class="{ 'is-loading': loading }" v-bind:disabled="loading ? true: false">
                                                <span class="icon"><i class="fas fa-lock"></i></span>
                                                <span>Sign in</span>
                                            </button>
                                        </p>
                                    </div>
                                </form>
                                <form v-on:submit.prevent="submitSignUp" v-show="isSignUpTabActive">
                                    <div class="box is-radiusless">
                                        <label class="label">Email</label>
                                        <p class="control has-icons-left" v-bind:class="{ 'has-icons-right' : validator.hasInvalidField('signUpEmail') }">
                                        <input class="input" type="email" ref="signUpEmail" maxlength="255" required v-bind:class="{ 'is-danger': validator.hasInvalidField('signUpEmail') }" v-bind:disabled="loading ? true: false" v-model="signUpEmail">
                                            <span class="icon is-small is-left"><i class="fas fa-envelope"></i></span>
                                            <span class="icon is-small is-right" v-show="validator.hasInvalidField('signUpEmail')"><i class="fas fa-warning"></i></span>
                                            <p class="help is-danger" v-show="validator.hasInvalidField('signUpEmail')">{{ validator.getInvalidFieldMessage('signUpEmail') }}</p>
                                        </p>
                                        <label class="label">Password</label>
                                        <p class="control has-icons-left" v-bind:class="{ 'has-icons-right' : validator.hasInvalidField('signUpPassword') }">
                                        <input class="input" type="password" required v-bind:class="{ 'is-danger': validator.hasInvalidField('signUpPassword') }" v-bind:disabled="loading ? true: false" v-model="signUpPassword">
                                            <span class="icon is-small is-left"><i class="fas fa-key"></i></span>
                                            <span class="icon is-small is-right" v-show="validator.hasInvalidField('signUpPassword')"><i class="fas fa-warning"></i></span>
                                            <p class="help is-danger" v-show="validator.hasInvalidField('signUpPassword')">{{ validator.getInvalidFieldMessage('signUpPassword') }}</p>
                                        </p>
                                        <hr>
                                        <p class="control">
                                            <button type="submit" class="button is-black" v-bind:class="{ 'is-loading': loading }" v-bind:disabled="loading ? true: false">
                                                <span class="icon"><i class="fas fa-plus-circle"></i></span>
                                                <span>Sign up</span>
                                            </button>
                                        </p>
                                    </div>
                                </form>
                                <p class="has-text-centered spieldose-margin-top-1rem">
                                    <a href="https://github.com/aportela/spieldose" target="_blank"><span class="icon is-small"><i class="fab fa-github"></i></span>Project page</a> | <a href="https://github.com/aportela" target="_blank">by alex</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <footer class="footer" v-if="hasAPIErrors">
                    <spieldose-api-error-component v-bind:apiError="apiError"></spieldose-api-error-component>
                </footer>
            </section>
        `;
    };

    /* signIn component */
    let module = Vue.component('spieldose-signin-component', {
        template: template(),
        mixins: [mixinValidations, mixinAPIError],
        created: function () {
            this.$nextTick(() => this.$refs.signInEmail.focus());
        },
        data: function () {
            return ({
                loading: false,
                signInEmail: null,
                signInPassword: null,
                allowSignUp: initialState.allowSignUp,
                signUpEmail: null,
                signUpPassword: null,
                tab: 'signin'
            });
        },
        computed: {
            isSignInTabActive: function () {
                return (this.tab == 'signin');
            },
            isSignUpTabActive: function () {
                return (this.tab == 'signup');
            }
        },
        methods: {
            changeTab: function (tab) {
                if (this.tab != tab) {
                    this.tab = tab;
                }
            },
            submitSignIn: function () {
                let self = this;
                self.loading = true;
                self.validator.clear();
                self.clearAPIErrors();
                spieldoseAPI.session.signIn(this.signInEmail, this.signInPassword, function (response) {
                    if (response.ok) {
                        self.$router.push({ name: 'dashboard' });
                    } else {
                        switch (response.status) {
                            case 400:
                                if (response.isFieldInvalid("email")) {
                                    self.validator.setInvalid("signInEmail", "API ERROR: invalid param");
                                } else if (response.isFieldInvalid("password")) {
                                    self.validator.setInvalid("signInPassword", "API ERROR: invalid param");
                                } else {
                                    self.setAPIError(response.getApiErrorData());
                                }
                                break;
                            case 404:
                                self.validator.setInvalid("signInEmail", "No account found with this email");
                                break;
                            case 401:
                                self.validator.setInvalid("signInPassword", "Incorrect password");
                                break;
                            default:
                                self.setAPIError(response.getApiErrorData());
                                break;
                        }
                        self.loading = false;
                    }
                });
            },
            submitSignUp: function () {
                let self = this;
                self.invalidSignUpUsername = false;
                self.invalidSignUpPassword = false;
                self.loading = true;
                self.clearAPIErrors();
                spieldoseAPI.session.signUp(this.signUpEmail, this.signUpPassword, function (response) {
                    if (response.ok) {
                        self.signInEmail = self.signUpEmail;
                        self.signInPassword = self.signUpPassword;
                        self.loading = false;
                        self.tab = 'signin';
                        self.submitSignIn();
                    } else {
                        switch (response.status) {
                            case 400:
                                if (response.isFieldInvalid("email")) {
                                    self.validator.setInvalid("signUpEmail", "API ERROR: invalid param");
                                } else if (response.isFieldInvalid("password")) {
                                    self.validator.setInvalid("signUpPassword", "API ERROR: invalid param");
                                } else {
                                    self.setAPIError(response.getApiErrorData());
                                }
                                break;
                            case 409:
                                self.validator.setInvalid("signUpEmail", "Email already used");
                                break;
                            default:
                                self.setAPIError(response.getApiErrorData());
                                break;
                        }
                        self.loading = false;
                    }
                });
            }
        }
    });

    return (module);
})();