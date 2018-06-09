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
        upgrade: {
            labels: {
                newDatabaseVersionAvailable: 'New database version available',
                upgradeRequired: 'An upgrade is required',
                executeThisCommandline: 'Execute this commandline:',
                updateNotRequired: 'Your system is up to date'
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
                byHour: 'By hour',
                playStatsByWeekday: 'Play stats by weekday',
                byWeekday: 'By weekday',
                byMonth: 'By month',
                byYear: 'By year',
                monday: 'Monday',
                tuesday: 'Tuesday',
                wednesday: 'Wednesday',
                thursday: 'Thursday',
                friday: 'Friday',
                saturday: 'Saturday',
                sunday: 'Sunday',
                playStatsByMonth: 'Play stats by month',
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
                playStatsByYear: 'Play stats by year'
            },
            errors: {
                notEnoughData: 'There is not enough data to calculate the statistics'
            }
        },
        player: {
            buttons: {
                shufflePlaylistHint: 'shuffle playlist',
                toggleRepeatHint: 'toggle repeat mode',
                previousTrackHint: 'go to previous track',
                pauseTrackHint: 'pause track',
                playTrackHint: 'play track',
                nextTrackHint: 'go to next track',
                unloveTrackHint: 'unlove this track',
                loveTrackHint: 'love this track',
                downloadTrackHint: 'download this track',
                toggleMuteHint: 'toggle mute'
            }
        },
        menu: {
            labels: {
                header: 'Menu',
                dashboard: 'Dashboard',
                currentPlaylist: 'Current playlist',
                search: 'Search',
                browseArtists: 'Browse artists',
                browseAlbums: 'Browse albums',
                browsePaths: 'Browse paths',
                browsePlaylists: 'Browse playlists',
                signOut: 'Sign out'
            }
        },
        pagination: {
            labels: {
                invalidPageOrNoResults: 'The specified page is incorrect or there are no results to display'
            },
            buttons: {
                previousPage: 'Previous',
                nextPage: 'Next'
            }
        },
        deleteConfirmationModal: {
            labels: {
                modalTitle: 'Confirmation required',
                modalBody: 'Are you sure you want to permanently remove this item ?'
            },
            buttons: {
                ok: 'Ok',
                cancel: 'Cancel'
            }
        },
        currentPlaylist: {
            labels: {
                sectionName: 'Now playing',
                tableHeaderTrack: 'Track',
                tableHeaderArtist: 'Artist',
                tableHeaderAlbum: 'Album',
                tableHeaderGenre: 'Genre',
                tableHeaderYear: 'Year',
                tableHeaderActions: 'Actions',
                moveElementUpHint: 'move up this track on playlist',
                moveElementDownHint: 'move down this track playlist',
                removeElementHint: 'remove this track from playlist',
                playThisTrackHint: 'play this track',
                nowPlayingClickToPauseHint: 'now playing, click to pause',
                pausedClickToResumeHint: 'paused, click to resume'
            },
            inputs: {
                playlistNamePlaceholder: 'type playlist name'
            },
            buttons: {
                savePlaylist: 'save playlist',
                unsetPlaylist: 'unset playlist',
                loadRandom: 'load random',
                clearPlaylist: 'clear playlist',
                shufflePlaylist: 'shuffle',
                previousTrack: 'previous',
                nextTrack: 'next',
                playTrack: 'play',
                resumeTrack: 'resume',
                pauseTrack: 'pause',
                stopTrack: 'stop',
                loveTrack: 'love',
                unloveTrack: 'unlove',
                downloadTrack: 'download',
            }
        },
        search: {
            labels: {
                sectionName: 'Search artists, albums, tracks, playlists'
            },
            tabs: {
                artists: 'Artists',
                albums: 'Albums',
                tracks: 'Tracks',
                playlists: 'Playlists'
            },
            inputs: {
                searchTextPlaceholder: 'search tracks, artists, albums, playlists'
            },
            buttons: {
                search: 'search'
            }
        },
        browseArtists: {
            labels: {
                sectionName: 'Browse artists',
            },
            inputs: {
                artistNamePlaceholder: 'search artist name...'
            },
            buttons: {
                search: 'search'
            },
            dropdowns: {
                filterAllArtists: 'All artists',
                filterNotScrapedArtists: 'Artists not scraped'
            }
        },
        browseArtist: {
            labels: {
                sectionName: 'Artist details',
                plays: 'plays',
                notPlayedYet: 'not played yet',
                tracksAlbumTableHeader: 'Album',
                tracksSectionYearTableHeader: 'Year',
                tracksSectionNumberTableHeader: 'Number',
                tracksSectionTitleTableHeader: 'Track',
                tracksSectionActionTableHeader: 'Actions',
                musicBrainzSearchArtistName: 'Artist name:',
                musicBrainzSearchArtistNamePlaceholder: 'search artist name...',
                musicBrainzSearchArtistMBId: 'Music Brainz id:',
                musicBrainzSearchArtistMBIdPlaceholder: 'set artist Music Brainz id'
            },
            tabs: {
                overview: 'Overview',
                bio: 'Bio',
                tracks: 'Tracks',
                albums: 'Albums',
                updateArtist: 'Update artist'
            },
            buttons: {
                search: 'search',
                searchOnMusicBrainz: 'Search on Music Brainz',
                save: 'save',
                clear: 'clear'
            }
        },
        browseAlbums: {
            labels: {
                sectionName: 'Browse albums',
                unknownArtist: 'unknown artist'
            },
            inputs: {
                albumNamePlaceholder: 'search album name...',
                yearPlaceholder: 'year (4 digits)',
                artistNamePlaceholder: 'search album artist name...'
            },
            buttons: {
                toggleAdvancedSearch: 'toggle advanced search',
                search: 'search'
            }
        },
        browsePaths: {
            labels: {
                sectionName: 'Browse paths',
                pathNameTableHeader: 'Path',
                trackCountTableHeader: 'Tracks',
                actionsTableHeader: 'Actions',
                playThisPath: 'Play this path',
                enqueueThisPath: 'Enqueue this path'
            },
            inputs: {
                pathNamePlaceholder: 'search path name...'
            },
            buttons: {
                search: 'search'
            }
        },
        browsePlaylists: {
            labels: {
                sectionName: 'Browse playlists'
            },
            inputs: {
                playlistNamePlaceholder: 'search playlist name...'
            },
            buttons: {
                search: 'search',
                play: 'play',
                remove: 'remove'
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
            playThisTrack: 'Play this track',
            enqueueThisTrack: 'Enqueue this track',
            downloadThisTrack: 'Download this track',
            navigateToArtistPage: 'Navigate to artist page',
            playThisAlbum: 'Play this album',
            enqueueThisAlbum: 'Enqueue this album',
            playThisPlaylist: 'Play this playlist',
            enqueueThisPlaylist: 'Enqueue this playlist',
            by: 'by',
            tracksCount: 'tracks'
        },
        commonMessages: {
            refreshData: 'Refresh data'
        }
    }
};
