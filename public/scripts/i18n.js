const messages = {
    en: {
        signIn: {
            labels: {
                tabLink: 'Sign in',
                email: 'Email',
                password: 'Password'
            },
            buttons: {
                submit: 'Sign in'
            },
            errorMessages: {
                userNotFound: 'No account found with this email',
                incorrectPassword: 'Incorrect password'
            }
        },
        signUp: {
            labels: {
                tabLink: 'Sign up',
                email: 'Email',
                password: 'Password'
            },
            buttons: {
                submit: 'Sign up'
            },
            errorMessages: {
                emailAlreadyUsed: 'Email already used'
            }
        },
        commonErrors: {
            invalidAPIParam: 'API ERROR: invalid param'
        },
        commonLabels: {
            slogan: '...music for the Masses',
            projectPageLinkLabel : 'Project page',
            authorLinkLabel: 'by alex',
        }
    }
};

// create VueI18n instance with options
const i18n = new VueI18n({
    locale: initialState.locale, // set locale
    messages, // set locale messages
});
