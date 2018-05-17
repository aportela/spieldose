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
        dashboard: {
            labels: {
                allTimeInterval: 'All Time',
                pastWeekInterval: 'Past week',
                pastMonthInterval: 'Past month',
                pastSemesterInterval: 'Past semester',
                pastYearInterval: 'Past year',
                playCount: 'plays',
                entityTracks: 'Tracks',
                entityArtists: 'Artists',
                entityAlbums: 'Albums',
                topPlayedTracks: 'Top played tracks',
                topPlayedArtists: 'Top played artists',
                topPlayedGenres: 'Top played genres',
                recentlyAdded: 'Recently added',
                recentlyPlayed: 'Recently played',
                playStatistics: 'Play statistics',
                byHour: 'by hour',
                playStatsByWeekday: 'play stats by weekday',
                byWeekday: 'by weekday',
                byMonth: 'by month',
                byYear: 'by year',
                monday: 'Monday',
                tuesday: 'Tuesday',
                wednesday: 'Wednesday',
                thursday: 'Thursday',
                friday: 'Friday',
                saturday: 'Saturday',
                sunday: 'Sunday',
                playStatsByMonth: 'play stats by month',
                january: 'January',
                february: 'February',
                march: 'March',
                april: 'April',
                may: 'May',
                june: 'June',
                july: 'July',
                august: 'August',
                september: 'September',
                october: 'October',
                november: 'November',
                december: 'December',
                playStatsByYear: 'play stats by year'
            },
            errors: {
                notEnoughData: 'there is not enough data to calculate the statistics'
            }
        },
        commonErrors: {
            invalidAPIParam: 'API ERROR: invalid param',
            invalidAPIResponse: 'API ERROR: invalid server response'
        },
        commonLabels: {
            slogan: '...music for the Masses',
            projectPageLinkLabel: 'Project page',
            authorLinkLabel: 'by alex',
            playThisTrack: 'play this track',
            enqueueThisTrack: 'enqueue this track',
            navigateToArtistPage: 'navigate to artist page',
            playThisAlbum: 'play this album',
            enqueueThisAlbum: 'enqueue this album'
        },
        commonMessages: {
            refreshData: 'refresh data'
        }
    }
};

// create VueI18n instance with options
const i18n = new VueI18n({
    locale: initialState.locale, // set locale
    messages, // set locale messages
});
