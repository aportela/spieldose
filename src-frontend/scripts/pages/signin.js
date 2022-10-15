import { default as spieldoseAPI } from '../api.js';
import { mixinValidations } from '../mixins.js';
import { default as blockTilesAlbumImages } from '../vue-components/tiles-album-images.js';

const template = function () {
    return `
        <!-- template credits: daniel (https://github.com/dansup) -->
        <div class="columns is-vcentered is-centered">
            <div class="column is-4-widescreen is-6-desktop is-8-tablet is-12-mobile">
                <section class="section">
                    <!-- Apple Music Sound Equalizer in SVG by Geoff Graham (https://codepen.io/geoffgraham/pen/XmMJqj) -->
                    <div class="columns is-centered">
                        <div class="column is-half">
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
                    </div>
                    <h1 class="title has-text-centered"><span class="icon is-medium"><i class="fas fa-music" aria-hidden="true"></i></span> Spieldose <span class="icon is-medium"><i class="fas fa-music" aria-hidden="true"></i></span></h1>
                    <h2 class="subtitle is-6 has-text-centered"><cite>{{ $t("commonLabels.slogan") }}</cite></h2>
                    <div class="box ">
                        <div class="tabs is-toggle is-radiusless ml-4 mb-2" v-if="allowSignUp">
                            <ul>
                                <li v-bind:class="{ 'is-active': isSignInTabActive }">
                                    <a v-on:click.prevent="changeTab('signin');">
                                        <span class="icon is-small"><i class="fas fa-user"></i></span>
                                        <span>{{ $t("signIn.labels.tabLink") }}</span>
                                    </a>
                                </li>
                                <li v-bind:class="{ 'is-active': isSignUpTabActive }">
                                    <a v-on:click.prevent="changeTab('signup');">
                                        <span class="icon is-small"><i class="fas fa-user-plus"></i></span>
                                        <span>{{ $t("signUp.labels.tabLink") }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <form v-on:submit.prevent="submitSignIn" v-show="isSignInTabActive">
                            <div class="box is-radiusless is-shadowless pt-0">
                                <div class="field">
                                    <label class="label is-hidden-mobile">{{ $t("signIn.labels.email") }}</label>
                                    <p class="block control has-icons-left" v-bind:class="{ 'has-icons-right' : validator.hasInvalidField('signInEmail') }">
                                        <input class="input is-rounded" type="email" ref="signInEmail" maxlength="255" required v-bind:class="{ 'is-danger': validator.hasInvalidField('signInEmail') }" v-bind:disabled="loading" v-model="signInEmail" :placeholder="$t('signIn.labels.email')">
                                        <span class="icon is-small is-left"><i class="fas fa-envelope"></i></span>
                                        <span class="icon is-small is-right" v-show="validator.hasInvalidField('signInEmail')"><i class="fas fa-warning"></i></span>
                                        <p class="help is-danger" v-show="validator.hasInvalidField('signInEmail')">{{ validator.getInvalidFieldMessage('signInEmail') }}</p>
                                    </p>
                                </div>
                                <div class="field">
                                    <label class="label is-hidden-mobile">{{ $t("signIn.labels.password") }}</label>
                                    <p class="block control has-icons-left" v-bind:class="{ 'has-icons-right' : validator.hasInvalidField('signInPassword') }">
                                        <input class="input is-rounded" type="password" ref="signInPassword" required v-bind:class="{ 'is-danger': validator.hasInvalidField('signInPassword') }" v-bind:disabled="loading" v-model="signInPassword" :placeholder="$t('signIn.labels.password')">
                                        <span class="icon is-small is-left"><i class="fas fa-key"></i></span>
                                        <span class="icon is-small is-right" v-show="validator.hasInvalidField('signInPassword')"><i class="fas fa-warning"></i></span>
                                        <p class="help is-danger" v-show="validator.hasInvalidField('signInPassword')">{{ validator.getInvalidFieldMessage('signInPassword') }}</p>
                                    </p>
                                </div>
                                <p class="control">
                                    <button type="submit" class="button is-black" v-bind:class="{ 'is-loading': loading }" v-bind:disabled="loading">
                                        <span class="icon"><i class="fas fa-lock"></i></span>
                                        <span>{{ $t("signIn.buttons.submit") }}</span>
                                    </button>
                                </p>
                            </div>
                        </form>
                        <form v-on:submit.prevent="submitSignUp" v-show="isSignUpTabActive">
                            <div class="box is-radiusless is-shadowless pt-0">
                                <div class="field">
                                    <label class="label is-hidden-mobile">{{ $t("signUp.labels.email") }}</label>
                                    <p class="block control has-icons-left" v-bind:class="{ 'has-icons-right' : validator.hasInvalidField('signUpEmail') }">
                                        <input class="input is-rounded" type="email" ref="signUpEmail" maxlength="255" required v-bind:class="{ 'is-danger': validator.hasInvalidField('signUpEmail') }" v-bind:disabled="loading" v-model="signUpEmail" :placeholder="$t('signIn.labels.email')">
                                        <span class="icon is-small is-left"><i class="fas fa-envelope"></i></span>
                                        <span class="icon is-small is-right" v-show="validator.hasInvalidField('signUpEmail')"><i class="fas fa-warning"></i></span>
                                        <p class="help is-danger" v-show="validator.hasInvalidField('signUpEmail')">{{ validator.getInvalidFieldMessage('signUpEmail') }}</p>
                                    </p>
                                </div>
                                <div class="field">
                                    <label class="label is-hidden-mobile">{{ $t("signUp.labels.password") }}</label>
                                    <p class="block control has-icons-left" v-bind:class="{ 'has-icons-right' : validator.hasInvalidField('signUpPassword') }">
                                        <input class="input is-rounded" type="password" ref="signUpPassword" required v-bind:class="{ 'is-danger': validator.hasInvalidField('signUpPassword') }" v-bind:disabled="loading" v-model="signUpPassword" :placeholder="$t('signIn.labels.password')">
                                        <span class="icon is-small is-left"><i class="fas fa-key"></i></span>
                                        <span class="icon is-small is-right" v-show="validator.hasInvalidField('signUpPassword')"><i class="fas fa-warning"></i></span>
                                        <p class="help is-danger" v-show="validator.hasInvalidField('signUpPassword')">{{ validator.getInvalidFieldMessage('signUpPassword') }}</p>
                                    </p>
                                </div>
                                <p class="control">
                                    <button type="submit" class="button is-black" v-bind:class="{ 'is-loading': loading }" v-bind:disabled="loading">
                                        <span class="icon"><i class="fas fa-plus-circle"></i></span>
                                        <span>{{ $t("signUp.buttons.submit") }}</span>
                                    </button>
                                </p>
                            </div>
                        </form>
                        <p class="has-text-centered">
                            <a href="https://github.com/aportela/spieldose" target="_blank"><span class="icon is-small mr-1"><i class="fab fa-github"></i></span>{{ $t("commonLabels.projectPageLinkLabel") }}</a> | <a href="https://github.com/aportela" target="_blank">{{ $t("commonLabels.authorLinkLabel") }}</a>
                        </p>
                    </div>
                </section>
            </div>
            <div class="column is-8 is-hidden-mobile is-hidden-tablet-only is-hidden-desktop-only container_tiles">
                <spieldose-tiles-album-images></spieldose-tiles-album-images>
            </div>
        </div>
    `;
};

export default {
    name: 'spieldose-signin-component',
    template: template(),
    mixins: [mixinValidations],
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
    mounted: function () {
        this.$nextTick(() => this.$refs.signInEmail.focus());
    },
    computed: {
        isSignInTabActive: function () {
            return (this.tab == 'signin');
        },
        isSignUpTabActive: function () {
            return (this.tab == 'signup');
        }
    },
    watch: {
        isSignInTabActive: function (newValue) {
            if (newValue) {
                this.$nextTick(() => this.$refs.signInEmail.focus());
            }
        },
        isSignUpTabActive: function (newValue) {
            if (newValue) {
                this.$nextTick(() => this.$refs.signUpEmail.focus());
            }
        }
    },
    components: {
        'spieldose-tiles-album-images': blockTilesAlbumImages
    },
    methods: {
        changeTab: function (tab) {
            if (this.tab != tab) {
                this.tab = tab;
            }
        },
        submitSignIn: function () {
            this.loading = true;
            this.validator.clear();
            this.$api.session.signIn(this.signInEmail, this.signInPassword).then(success => {
                this.loading = false;
                this.$router.push({ name: 'dashboard' });
            }).catch(error => {
                switch (error.response.status) {
                    case 400:
                        if (error.isFieldInvalid('email')) {
                            this.validator.setInvalid('signInEmail', this.$t('commonErrors.invalidAPIParam'));
                            this.$nextTick(() => this.$refs.signInEmail.focus());
                        } else if (error.isFieldInvalid("password")) {
                            this.validator.setInvalid('signInPassword', this.$t('commonErrors.invalidAPIParam'));
                            this.$nextTick(() => this.$refs.signInPassword.focus());
                        } else {
                            this.setAPIError(error.getApiErrorData());
                        }
                        break;
                    case 404:
                        this.validator.setInvalid('signInEmail', this.$t('signIn.errorMessages.userNotFound'));
                        this.$nextTick(() => this.$refs.signInEmail.focus());
                        break;
                    case 401:
                        this.validator.setInvalid('signInPassword', this.$t('signIn.errorMessages.incorrectPassword'));
                        this.$nextTick(() => this.$refs.signInPassword.focus());
                        break;
                    default:
                        this.setAPIError(error.getApiErrorData());
                        break;
                }
                this.loading = false;
            });
        },
        submitSignUp: function () {
            this.invalidSignUpUsername = false;
            this.invalidSignUpPassword = false;
            this.loading = true;
            this.$api.session.signUp(this.signUpEmail, this.signUpPassword).then(success => {
                this.signInEmail = this.signUpEmail;
                this.signInPassword = this.signUpPassword;
                this.loading = false;
                this.tab = 'signin';
                this.submitSignIn();
                this.loading = false;
            }).catch(error => {
                switch (error.response.status) {
                    case 400:
                        if (error.isFieldInvalid('email')) {
                            this.validator.setInvalid('signUpEmail', this.$t('commonErrors.invalidAPIParam'));
                            this.$nextTick(() => this.$refs.signUpEmail.focus());
                        } else if (error.isFieldInvalid('password')) {
                            this.validator.setInvalid('signUpPassword', this.$t('commonErrors.invalidAPIParam'));
                            this.$nextTick(() => this.$refs.signUpPassword.focus());
                        } else {
                            this.setAPIError(error.getApiErrorData());
                        }
                        break;
                    case 409:
                        this.validator.setInvalid('signUpEmail', this.$t('signUp.errorMessages.emailAlreadyUsed'));
                        this.$nextTick(() => this.$refs.signUpEmail.focus());
                        break;
                    default:
                        this.setAPIError(error.getApiErrorData());
                        break;
                }
                this.loading = false;
            });
        }
    }
}