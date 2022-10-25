export default {
    gl: {
        signIn: {
            labels: {
                tabLink: 'Iniciar sesión',
                email: 'Correo electrónico',
                password: 'Contrasinal'
            },
            buttons: {
                submit: 'Iniciar sesión'
            },
            errorMessages: {
                userNotFound: 'Non existe ningunha conta con ese correo electrónico asociado',
                incorrectPassword: 'Contrasinal incorrecta'
            }
        },
        signUp: {
            labels: {
                tabLink: 'Crear conta',
                email: 'Correo electrónico',
                password: 'Contrasinal'
            },
            buttons: {
                submit: 'Crear conta'
            },
            errorMessages: {
                emailAlreadyUsed: 'O correo electrónico especificado xa está en uso'
            }
        },
        upgrade: {
            labels: {
                newDatabaseVersionAvailable: 'Atopouse disponible unha nova versión da base de datos',
                upgradeRequired: 'Requírese actualizar',
                executeThisCommandline: 'Execute esta línea de comandos:',
                updateNotRequired: 'O seu sistema está actualizado'
            }
        },
        dashboard: {
            labels: {
                allTimeInterval: 'Sempre',
                pastWeekInterval: 'A semana pasada',
                pastMonthInterval: 'O mes pasado',
                pastSemesterInterval: 'O semestre pasado',
                pastYearInterval: 'O ano pasado',
                playCount: 'reproduccións',
                entityTracks: 'Cancions',
                entityArtists: 'Artistas',
                entityAlbums: 'Álbumes',
                topPlayedTracks: 'Cancións máis escoitadas',
                topPlayedArtists: 'Artistas máis escoitados',
                topPlayedGenres: 'Xéneros máis escoitados',
                recentlyAdded: 'Engadido/a recentemente',
                recentlyPlayed: 'Escoitado/a recentemente',
                playStatistics: 'Estadísticas de reproduccións',
                byHour: 'Por hora',
                playStatsByWeekday: 'Estadísticas de reproduccións semanais',
                byWeekday: 'Por día da semana',
                byMonth: 'Por mes',
                byYear: 'Por ano',
                monday: 'Luns',
                tuesday: 'Martes',
                wednesday: 'Mércoles',
                thursday: 'Xoves',
                friday: 'Venres',
                saturday: 'Sábado',
                sunday: 'Domingo',
                playStatsByMonth: 'Estadísticas de reproduccións mensuais',
                january: 'Xaneiro',
                february: 'Febreiro',
                march: 'Marzo',
                april: 'Abril',
                may: 'Maio',
                june: 'Xuño',
                july: 'Xullo',
                august: 'Agosto',
                september: 'Setembro',
                october: 'Outubro',
                november: 'Novembro',
                december: 'Decembro',
                playStatsByYear: 'Estadísticas de reproduccións anuais'
            },
            errors: {
                notEnoughData: 'Non se recolectaron datos suficientes pra xear as estadísticas'
            }
        },
        player: {
            buttons: {
                shufflePlaylistHint: 'reordear aleatoriamente a lista actual',
                toggleRepeatHint: 'cambiar o modo de repetición',
                previousTrackHint: 'ir á canción anterior',
                pauseTrackHint: 'pausar a canción actual',
                playTrackHint: 'reproducir a canción actual',
                nextTrackHint: 'ir á seguinte canción',
                unloveTrackHint: 'desmarcar a canción actual como favorita',
                loveTrackHint: 'marcar a canción actual como favorita',
                downloadTrackHint: 'descargar a canción actual',
                toggleMuteHint: 'silenciar/restaurar volumen'
            }
        },
        menu: {
            labels: {
                header: 'Menu',
                dashboard: 'Estadísticas',
                currentPlaylist: 'Lista de reproducción actual',
                search: 'Procura',
                browseArtists: 'Artistas',
                browseAlbums: 'Álbumes',
                browsePaths: 'Directorios',
                browsePlaylists: 'Listas de reproducción',
                browseRadioStations: 'Estacións de radio',
                profile: 'O meu perfil',
                signOut: 'Rematar sesión'
            }
        },
        pagination: {
            labels: {
                invalidPageOrNoResults: 'A páxina especificada é incorrecta ou non se atoparon resultados'
            },
            buttons: {
                previousPage: 'Anterior',
                nextPage: 'Seguinte'
            }
        },
        deleteConfirmationModal: {
            labels: {
                modalTitle: 'Confirmación requerida',
                modalBody: '¿ Está seguro de que desexa eliminar permanentemente este elemento ?'
            },
            buttons: {
                ok: 'Si',
                cancel: 'Cancelar'
            }
        },
        currentPlaylist: {
            labels: {
                sectionName: 'Lista de reproducción actual',
                tableHeaderTrack: 'Título',
                tableHeaderArtist: 'Artista',
                tableHeaderAlbum: 'Álbum',
                tableHeaderGenre: 'Xénero',
                tableHeaderYear: 'Ano',
                tableHeaderActions: 'Accións',
                moveElementUpHint: 'subir esta canción na lista de reproducción',
                moveElementDownHint: 'baixar esta canción na lista de reproducción',
                removeElementHint: 'eliminar esta canción da lista de reproducción',
                playThisTrackHint: 'escoitar esta canción',
                nowPlayingClickToPauseHint: 'reproducindo, faga click pra pausar',
                pausedClickToResumeHint: 'en pausa, faga click pra resumir reproducción'
            },
            inputs: {
                playlistNamePlaceholder: 'teclee o nombre da lista de reproducción'
            },
            buttons: {
                savePlaylist: 'grabar lista',
                unsetPlaylist: 'desenlazar lista',
                loadRandom: 'cargar cancións aleatorias',
                clearPlaylist: 'vaciar lista',
                repeat: 'repetir',
                shufflePlaylist: 'reordear aleatoriamente',
                previousTrack: 'anterior',
                nextTrack: 'seguinte',
                playTrack: 'reproducir',
                resumeTrack: 'resumir reproducción',
                pauseTrack: 'pausar',
                stopTrack: 'deter',
                loveTrack: 'favorita',
                unloveTrack: 'favorita',
                downloadTrack: 'descargar',
            }
        },
        search: {
            labels: {
                sectionName: 'Procurar artistas, álbumes, cancións e listas de reproducción'
            },
            tabs: {
                artists: 'Artistas',
                albums: 'Álbumes',
                tracks: 'Cancións',
                playlists: 'Listas de reproducción'
            },
            inputs: {
                searchTextPlaceholder: 'procurar artistas, álbumes, cancións y listas de reproducción'
            },
            buttons: {
                search: 'procurar'
            }
        },
        browseArtists: {
            labels: {
                sectionName: 'Artistas',
            },
            inputs: {
                artistNamePlaceholder: 'procure artista por nome...'
            },
            buttons: {
                search: 'procurar'
            },
            dropdowns: {
                filterAllArtists: 'Tódolos artistas',
                filterNotScrapedArtists: 'Artistas sen metadatos'
            }
        },
        browseArtist: {
            labels: {
                sectionName: 'Detalles do artista',
                plays: 'reproduccións',
                notPlayedYet: 'nunca se escoitou',
                tracksAlbumTableHeader: 'Álbum',
                tracksSectionYearTableHeader: 'Ano',
                tracksSectionNumberTableHeader: 'Número',
                tracksSectionTitleTableHeader: 'Canción',
                tracksSectionActionTableHeader: 'Accións',
                musicBrainzSearchArtistName: 'Nome do artista:',
                musicBrainzSearchArtistNamePlaceholder: 'procurar artista por nome...',
                musicBrainzSearchArtistMBId: 'Identificador Music Brainz:',
                musicBrainzSearchArtistMBIdPlaceholder: 'establecer o identificador Music Brainz do artista'
            },
            tabs: {
                overview: 'Resumen',
                bio: 'Biografía',
                tracks: 'Cancións',
                albums: 'Álbumes',
                updateArtist: 'Actualizar metadatos'
            },
            buttons: {
                search: 'procurar',
                searchOnMusicBrainz: 'procurar en Music Brainz',
                save: 'grabar',
                clear: 'eliminar'
            }
        },
        browseAlbums: {
            labels: {
                sectionName: 'Álbumes',
                unknownArtist: 'artista descoñecido'
            },
            inputs: {
                albumNamePlaceholder: 'procurar álbum por nome...',
                yearPlaceholder: 'ano (4 díxitos)',
                artistNamePlaceholder: 'procurar álbum por nome do artista...'
            },
            buttons: {
                toggleAdvancedSearch: 'mostrar/ocultar procura avanzada',
                search: 'procurar'
            }
        },
        browsePaths: {
            labels: {
                sectionName: 'Directorios',
                pathNameTableHeader: 'Ruta',
                trackCountTableHeader: 'Cancións',
                actionsTableHeader: 'Accións',
                playThisPath: 'Escoitar todas as cancións desta ruta',
                enqueueThisPath: 'Engadir todas as cancións desta ruta á cola de reproducción actual'
            },
            inputs: {
                pathNamePlaceholder: 'procurar ruta por nome...'
            },
            buttons: {
                search: 'procurar'
            }
        },
        browsePlaylists: {
            labels: {
                sectionName: 'Listas de reproducción'
            },
            inputs: {
                playlistNamePlaceholder: 'procurar lista de reproducción por nome...'
            },
            buttons: {
                search: 'procurar',
                play: 'escoitar',
                remove: 'eliminar'
            }
        },
        browseRadioStations: {
            labels: {
                sectionName: 'Estacións de radio',
                radioStationName: 'Nome da estación:',
                radioStationUrl: 'Dirección:',
                radioStationImage: 'Imaxen:'
            },
            inputs: {
                radioStationSearchNamePlaceholder: 'procurar estación de radio por nome...',
                radioStationNamePlaceholder: 'teclee o nombre da estación de radio',
                radioStationPlaceholderUrl: 'teclee a dirección da estación de radio (stream directo / lista de reproducción, formatos: m3u, pls)',
                radioStationPlaceholderImage: 'teclee (opcional) a dirección da imaxen',
            },
            selects: {
                optionDirectStream: 'Tipo da estación: Stream directo',
                optionM3U: 'Tipo da estación: lista de reproducción (formato m3u)',
                optionPLS: 'Tipo da estación: Lista de reproducción (formato pls)',
            },
            buttons: {
                add: 'engadir',
                search: 'procurar',
                play: 'escoitar',
                update: 'actualizar',
                remove: 'eliminar',
                save: 'grabar',
                cancel: 'cancelar'
            }
        },
        profile: {
            labels: {
                sectionName: 'O meu perfil',
                email: 'Email',
                newPassword: 'Nova contrasinal',
                confirmNewPassword: 'Confirme a nova contrasinal'
            },
            buttons: {
                submit: 'gardar cambios',
                linkLastFMAccount: 'Enlazar a unha conta en Last.FM (requerido pra scrobbling)'
            },
            errorMessages: {
                'passwordsDontMatch': 'As contrasinais non coinciden'
            }
        },
        commonErrors: {
            invalidAPIParam: 'API ERROR: parámetro incorrecto',
            invalidAPIResponse: 'API ERROR: resposta do servidor descoñecida'
        },
        commonLabels: {
            slogan: '...música pra todos',
            projectPageLinkLabel: 'Páxina do proxecto',
            authorLinkLabel: 'por alex',
            playThisTrack: 'Escoitar esta canción',
            enqueueThisTrack: 'Engadir esta canción á cola de reproducción actual',
            downloadThisTrack: 'Descargar esta canción',
            navigateToArtistPage: 'Visitar a páxina do artista',
            playThisAlbum: 'Escoitar este álbum',
            enqueueThisAlbum: 'Engadir este álbum á cola de reproducción actual',
            playThisPlaylist: 'Escoitar esta lista de reproducción',
            enqueueThisPlaylist: 'Engadir esta lista de reproducción á cola de reproducción actual',
            by: 'por',
            tracksCount: 'cancións',
            repeatModeNone: 'nada',
            repeatModeTrack: 'canción',
            repeatModeAll: 'todo',
            remoteRadioStation: 'Estación de radio remota'
        },
        commonMessages: {
            refreshData: 'Refrescar datos'
        }
    }
};
