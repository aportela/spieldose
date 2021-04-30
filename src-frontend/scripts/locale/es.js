export default {
    es: {
        signIn: {
            labels: {
                tabLink: 'Iniciar sesión',
                email: 'Correo electrónico',
                password: 'Contraseña'
            },
            buttons: {
                submit: 'Iniciar sesión'
            },
            errorMessages: {
                userNotFound: 'No existe ninguna cuenta con ese correo electrónico asociado',
                incorrectPassword: 'Clave incorrecta'
            }
        },
        signUp: {
            labels: {
                tabLink: 'Crear cuenta',
                email: 'Correo electrónico',
                password: 'Contraseña'
            },
            buttons: {
                submit: 'Crear cuenta'
            },
            errorMessages: {
                emailAlreadyUsed: 'El correo electrónico especificado ya está en uso'
            }
        },
        upgrade: {
            labels: {
                newDatabaseVersionAvailable: 'Hay disponible una nueva versión de la base de datos',
                upgradeRequired: 'Se requiere actualizar',
                executeThisCommandline: 'Ejecute esta línea de comandos:',
                updateNotRequired: 'Su sistema está actualizado'
            }
        },
        dashboard: {
            labels: {
                allTimeInterval: 'Siempre',
                pastWeekInterval: 'La semana pasada',
                pastMonthInterval: 'El mes pasado',
                pastSemesterInterval: 'El semestre pasado',
                pastYearInterval: 'El año pasado',
                playCount: 'reproducciones',
                entityTracks: 'Canciones',
                entityArtists: 'Artistas',
                entityAlbums: 'Álbumes',
                topPlayedTracks: 'Canciones más escuchadas',
                topPlayedArtists: 'Artistas más escuchados',
                topPlayedGenres: 'Géneros más escuchados',
                recentlyAdded: 'Añadido/a recientemente',
                recentlyPlayed: 'Escuchado/a recientemente',
                playStatistics: 'Estadísticas de reproducciones',
                byHour: 'Por hora',
                playStatsByWeekday: 'Estadísticas de reproducciones semanales',
                byWeekday: 'Por día de la semana',
                byMonth: 'Por mes',
                byYear: 'Por año',
                monday: 'Lunes',
                tuesday: 'Martes',
                wednesday: 'Miércoles',
                thursday: 'Jueves',
                friday: 'Viernes',
                saturday: 'Sábado',
                sunday: 'Domingo',
                playStatsByMonth: 'Estadísticas de reproducciones mensuales',
                january: 'Enero',
                february: 'Febrero',
                march: 'Marzo',
                april: 'Abril',
                may: 'Mayo',
                june: 'Junio',
                july: 'Julio',
                august: 'Agosto',
                september: 'Septiembre',
                october: 'Octubre',
                november: 'Noviembre',
                december: 'Diciembre',
                playStatsByYear: 'Estadísticas de reproducciones anuales'
            },
            errors: {
                notEnoughData: 'No se han recolectado datos suficientes para generar las estadísticas'
            }
        },
        player: {
            buttons: {
                shufflePlaylistHint: 'reordenar aleatoriamente la lista actual',
                toggleRepeatHint: 'cambiar el modo de repetición',
                previousTrackHint: 'ir a la canción anterior',
                pauseTrackHint: 'pausar la canción actual',
                playTrackHint: 'reproducir la canción actual',
                nextTrackHint: 'ir a la siguiente canción',
                unloveTrackHint: 'desmarcar la canción actual como favorita',
                loveTrackHint: 'marcar la canción actual como favorita',
                downloadTrackHint: 'descargar la canción actual',
                toggleMuteHint: 'silenciar/restaurar volumen'
            }
        },
        menu: {
            labels: {
                header: 'Menu',
                dashboard: 'Estadísticas',
                currentPlaylist: 'Lista de reproducción actual',
                search: 'Búsqueda',
                browseArtists: 'Artistas',
                browseAlbums: 'Álbumes',
                browsePaths: 'Directorios',
                browsePlaylists: 'Listas de reproducción',
                browseRadioStations: 'Estaciones de radio',
                signOut: 'Finalizar sesión'
            }
        },
        pagination: {
            labels: {
                invalidPageOrNoResults: 'La página especificada es incorrecta o no se han encontrado resultados'
            },
            buttons: {
                previousPage: 'Anterior',
                nextPage: 'Siguiente'
            }
        },
        deleteConfirmationModal: {
            labels: {
                modalTitle: 'Confirmación requerida',
                modalBody: '¿ Está seguro de que desea eliminar permanentemente este elemento ?'
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
                tableHeaderGenre: 'Género',
                tableHeaderYear: 'Año',
                tableHeaderActions: 'Acciones',
                moveElementUpHint: 'subir esta canción en la lista de reproducción',
                moveElementDownHint: 'bajar esta canción en la lista de reproducción',
                removeElementHint: 'eliminar esta canción de la lista de reproducción',
                playThisTrackHint: 'escuchar esta canción',
                nowPlayingClickToPauseHint: 'reproduciendo, haga click para pausar',
                pausedClickToResumeHint: 'en pausa, haga click para resumir reproducción'
            },
            inputs: {
                playlistNamePlaceholder: 'teclee el nombre de la lista de reproducción'
            },
            buttons: {
                savePlaylist: 'grabar lista',
                unsetPlaylist: 'desenlazar lista',
                loadRandom: 'cargar canciones aleatorias',
                clearPlaylist: 'vaciar lista',
                repeat: 'repetir',
                shufflePlaylist: 'reordenar aleatoriamente',
                previousTrack: 'anterior',
                nextTrack: 'siguiente',
                playTrack: 'reproducir',
                resumeTrack: 'resumir reproducción',
                pauseTrack: 'pausar',
                stopTrack: 'detener',
                loveTrack: 'favorita',
                unloveTrack: 'favorita',
                downloadTrack: 'descargar',
            }
        },
        search: {
            labels: {
                sectionName: 'Buscar artistas, álbumes, canciones y listas de reproducción'
            },
            tabs: {
                artists: 'Artistas',
                albums: 'Álbumes',
                tracks: 'Canciones',
                playlists: 'Listas de reproducción'
            },
            inputs: {
                searchTextPlaceholder: 'buscar artistas, álbumes, canciones y listas de reproducción'
            },
            buttons: {
                search: 'buscar'
            }
        },
        browseArtists: {
            labels: {
                sectionName: 'Artistas',
            },
            inputs: {
                artistNamePlaceholder: 'teclee el nombre del artista...'
            },
            buttons: {
                search: 'buscar'
            },
            dropdowns: {
                filterAllArtists: 'Todos los artistas',
                filterNotScrapedArtists: 'Artistas sin metadatos'
            }
        },
        browseArtist: {
            labels: {
                sectionName: 'Detalles del artista',
                plays: 'reproducciones',
                notPlayedYet: 'nunca se ha escuchado',
                tracksAlbumTableHeader: 'Álbum',
                tracksSectionYearTableHeader: 'Año',
                tracksSectionNumberTableHeader: 'Número',
                tracksSectionTitleTableHeader: 'Canción',
                tracksSectionActionTableHeader: 'Acciones',
                musicBrainzSearchArtistName: 'Nombre del artista:',
                musicBrainzSearchArtistNamePlaceholder: 'buscar artista por nombre...',
                musicBrainzSearchArtistMBId: 'Identificador Music Brainz:',
                musicBrainzSearchArtistMBIdPlaceholder: 'establecer el identificador Music Brainz del artista'
            },
            tabs: {
                overview: 'Resumen',
                bio: 'Biografía',
                tracks: 'Canciones',
                albums: 'Álbumes',
                updateArtist: 'Actualizar metadatos'
            },
            buttons: {
                search: 'buscar',
                searchOnMusicBrainz: 'buscar en Music Brainz',
                save: 'grabar',
                clear: 'eliminar'
            }
        },
        browseAlbums: {
            labels: {
                sectionName: 'Álbumes',
                unknownArtist: 'artista desconocido'
            },
            inputs: {
                albumNamePlaceholder: 'buscar álbum por nombre...',
                yearPlaceholder: 'año (4 dígitos)',
                artistNamePlaceholder: 'buscar álbum por nombre de artista...'
            },
            buttons: {
                toggleAdvancedSearch: 'mostrar/ocultar búsqueda avanzada',
                search: 'buscar'
            }
        },
        browsePaths: {
            labels: {
                sectionName: 'Directorios',
                pathNameTableHeader: 'Ruta',
                trackCountTableHeader: 'Canciones',
                actionsTableHeader: 'Acciones',
                playThisPath: 'Escuchar todas las canciones de esta ruta',
                enqueueThisPath: 'Añadir todas las canciones de esta ruta a la cola de reproducción actual'
            },
            inputs: {
                pathNamePlaceholder: 'buscar ruta por nombre...'
            },
            buttons: {
                search: 'buscar'
            }
        },
        browsePlaylists: {
            labels: {
                sectionName: 'Listas de reproducción'
            },
            inputs: {
                playlistNamePlaceholder: 'buscar lista de reproducción por nombre...'
            },
            buttons: {
                search: 'buscar',
                play: 'escuchar',
                remove: 'eliminar'
            }
        },
        browseRadioStations: {
            labels: {
                sectionName: 'Estaciones de radio',
                radioStationName: 'Nombre de la estación:',
                radioStationUrl: 'Dirección:',
                radioStationImage: 'Imagen:'
            },
            inputs: {
                radioStationSearchNamePlaceholder: 'buscar estación de radio por nombre...',
                radioStationNamePlaceholder: 'teclee el nombre de la estación de radio',
                radioStationPlaceholderUrl: 'teclee la dirección de la estación de radio (stream directo / lista de reproducción, formatos: m3u, pls)',
                radioStationPlaceholderImage: 'teclee (opcional) la dirección de la imagen',
            },
            selects: {
                optionDirectStream: 'Tipo de la estación: Stream directo',
                optionM3U: 'Tipo de la estación: lista de reproducción (formato m3u)',
                optionPLS: 'Tipo de la estación: Lista de reproducción (formato pls)',
            },
            buttons: {
                add: 'añadir',
                search: 'buscar',
                play: 'escuchar',
                update: 'actualizar',
                remove: 'eliminar',
                save: 'grabar',
                cancel: 'cancelar'
            }
        },
        commonErrors: {
            invalidAPIParam: 'API ERROR: parámetro incorrecto',
            invalidAPIResponse: 'API ERROR: respuesta del servidor desconocida'
        },
        commonLabels: {
            slogan: '...música para todos',
            projectPageLinkLabel: 'Página del proyecto',
            authorLinkLabel: 'por alex',
            playThisTrack: 'Escuchar esta canción',
            enqueueThisTrack: 'Añadir esta canción a la cola de reproducción actual',
            downloadThisTrack: 'Descargar esta canción',
            navigateToArtistPage: 'Visitar la página del artista',
            playThisAlbum: 'Escuchar este álbum',
            enqueueThisAlbum: 'Añadir este álbum a la cola de reproducción actual',
            playThisPlaylist: 'Escuchar esta lista de reproducción',
            enqueueThisPlaylist: 'Añadir esta lista de reproducción a la cola de reproducción actual',
            by: 'por',
            tracksCount: 'canciones',
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
