import { mixinValidations } from '../mixins.js';

const template = function () {
    return `
        <h4>{{ $t("profile.labels.sectionName") }}</h4>

        <div class="box is-radiusless is-shadowless pt-0">
            <div class="field">
                <label class="label is-hidden-mobile">{{ $t("profile.labels.email") }}</label>
                <p class="block control has-icons-left" v-bind:class="{ 'has-icons-right' : validator.hasInvalidField('email') }">
                    <input class="input is-rounded" type="email" ref="email" maxlength="255" required v-bind:class="{ 'is-danger': validator.hasInvalidField('email') }" v-bind:disabled="loading" v-model="email" :placeholder="$t('signIn.labels.email')">
                    <span class="icon is-small is-left"><i class="fas fa-envelope"></i></span>
                    <span class="icon is-small is-right" v-show="validator.hasInvalidField('email')"><i class="fas fa-warning"></i></span>
                    <p class="help is-danger" v-show="validator.hasInvalidField('email')">{{ validator.getInvalidFieldMessage('email') }}</p>
                </p>
            </div>
            <div class="field">
                <label class="label is-hidden-mobile">{{ $t("profile.labels.newPassword") }}</label>
                <p class="block control has-icons-left" v-bind:class="{ 'has-icons-right' : validator.hasInvalidField('newPassword') }">
                    <input class="input is-rounded" type="password" ref="newPassword" required v-bind:class="{ 'is-danger': validator.hasInvalidField('newPassword') }" v-bind:disabled="loading" v-model="newPassword" :placeholder="$t('profile.labels.newPassword')">
                    <span class="icon is-small is-left"><i class="fas fa-key"></i></span>
                    <span class="icon is-small is-right" v-show="validator.hasInvalidField('newPassword')"><i class="fas fa-warning"></i></span>
                    <p class="help is-danger" v-show="validator.hasInvalidField('newPassword')">{{ validator.getInvalidFieldMessage('newPassword') }}</p>
                </p>
            </div>
            <div class="field">
                <label class="label is-hidden-mobile">{{ $t("profile.labels.confirmNewPassword") }}</label>
                <p class="block control has-icons-left" v-bind:class="{ 'has-icons-right' : validator.hasInvalidField('confirmNewPassword') }">
                    <input class="input is-rounded" type="password" ref="confirmNewPassword" required v-bind:class="{ 'is-danger': validator.hasInvalidField('password') }" v-bind:disabled="loading" v-model="confirmNewPassword" :placeholder="$t('profile.labels.confirmNewPassword')">
                    <span class="icon is-small is-left"><i class="fas fa-key"></i></span>
                    <span class="icon is-small is-right" v-show="validator.hasInvalidField('confirmNewPassword')"><i class="fas fa-warning"></i></span>
                    <p class="help is-danger" v-show="validator.hasInvalidField('confirmNewPassword')">{{ validator.getInvalidFieldMessage('confirmNewPassword') }}</p>
                </p>
            </div>

            <div class="field has-addons">
                <p class="control">
                    <button type="submit" class="button is-dark" v-bind:class="{ 'is-loading': loading }" v-bind:disabled="loading">
                        <span class="icon"><i class="fas fa-save"></i></span>
                        <span>{{ $t("profile.buttons.submit") }}</span>
                    </button>
                </p>
                <p class="control">
                    <button type="button" class="button is-pink" v-bind:class="{ 'is-loading': loading }" v-bind:disabled="loading || ! hasLastFMAPIKey" @click.prevent="onLinkLastFM">
                        <span class="icon"><i class="fab fa-lastfm"></i></span>
                        <span>{{ $t("profile.buttons.linkLastFMAccount") }}</span>
                    </button>
                </p>
            </div>
        </div>
    `;
};

export default {
    name: 'spieldose-page-profile',
    template: template(),
    mixins: [mixinValidations],
    data: function () {
        return ({
            loading: false,
            email: null,
            newPassword: null,
            confirmNewPassword: null
        });
    },
    computed: {
        hasLastFMAPIKey: function () {
            return (initialState && initialState.lastFMAPIKey);
        }
    },
    created: function () {
        this.get();
    },
    mounted: function () {
        this.$nextTick(() => this.$refs.email.focus());
    },
    watch: {
        newPassword: function (newValue, oldValue) {
            this.validator.clear();
            if (newValue != this.confirmNewPassword) {
                this.validator.setInvalid('newPassword', this.$t('profile.errorMessages.passwordsDontMatch'));
                this.validator.setInvalid('confirmNewPassword', this.$t('profile.errorMessages.passwordsDontMatch'));
            }
        },
        confirmNewPassword: function (newValue, oldValue) {
            this.validator.clear();
            if (newValue != this.newPassword) {
                this.validator.setInvalid('newPassword', this.$t('profile.errorMessages.passwordsDontMatch'));
                this.validator.setInvalid('confirmNewPassword', this.$t('profile.errorMessages.passwordsDontMatch'));
            }
        }
    },
    methods: {
        get: function () {

        },
        onLinkLastFM: function () {
            window.open("http://www.last.fm/api/auth/?api_key=" + initialState.lastFMAPIKey + '&cb=http://localhost:8080/OK')
        }
    }
}