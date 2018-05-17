/**
 * create & return a player data object
 */
const getPlayerData = (function () {
    "use strict";

    let playerData = {
        loading: false,
        isPlaying: false,
        isPaused: false,
        isStopped: true,
        repeatTracksMode: 'none', // none | track | all
        currentPlaylist: {
            id: null,
            name: null
        },
        currentTrack: {
            index: 0,
            track: null
        },
        playback: {},
        tracks: []
    };

    /**
     * shuffle array
     * https://stackoverflow.com/a/6274381
     * @param {*} a
     */
    function shuffle(a) {
        var j, x, i;
        for (i = a.length - 1; i > 0; i--) {
            j = Math.floor(Math.random() * (i + 1));
            x = a[i];
            a[i] = a[j];
            a[j] = x;
        }
        return a;
    };

    playerData.download = function(trackId) {
        if (trackId) {
            window.location = "api/track/get/" + trackId;
        }
    };
    playerData.love = function (track) {
        this.loading = true;
        spieldoseAPI.track.love(track.id, function (response) {
            if (response.ok) {
                playerData.loading = false;
                track.loved = response.body.loved;
            } else {
                // TODO: ERRORS
                playerData.loading = false;
            }
        });
    };
    playerData.unlove = function (track) {
        this.loading = true;
        spieldoseAPI.track.unlove(track.id, function (response) {
            if (response.ok) {
                playerData.loading = false;
                track.loved = response.body.loved;
            } else {
                // TODO: ERRORS
                playerData.loading = false;
            }
        });
    };


    playerData.currentTrack.unset = () => {
        playerData.currentTrackindex = 0;
        playerData.currentTrack.track = null;
    };
    playerData.currentTrack.download = function () {
        if (playerData.currentTrack.track) {
            download(playerData.currentTrack.track.id);
        }
    };
    playerData.currentTrack.setLoved = function() {
        if (playerData.currentTrack.track) {
            playerData.love(playerData.currentTrack.track);
        }
    };
    playerData.currentTrack.unSetLoved = function() {
        if (playerData.currentTrack.track) {
            playerData.unlove(playerData.currentTrack.track);
        }
    };

    playerData.currentPlaylist.unset = () => {
        playerData.currentPlaylist.id = null;
        playerData.currentPlaylist.name = null;
    };
    playerData.currentPlaylist.empty = () => {
        playerData.currentTrack.unset();
        playerData.tracks = [];
    };
    playerData.currentPlaylist.replace = function (tracks) {
        playerData.currentPlaylist.empty();
        playerData.tracks = tracks;
        playerData.play();
    };
    playerData.currentPlaylist.enqueue = function (tracks) {
        if (tracks.length > 0) {
            for (var i = 0; i < tracks.length; i++) {
                playerData.tracks.push(tracks[i]);
            }
        }
    };
    playerData.currentPlaylist.isSet = function () {
        return(playerData.currentPlaylist.id ? true: false);
    };
    playerData.currentPlaylist.set = function (id, name) {
        playerData.currentPlaylist.id = id;
        playerData.currentPlaylist.name = name;
    };
    playerData.currentPlaylist.unset = function () {
        playerData.currentPlaylist.id = null;
        playerData.currentPlaylist.name = null;
    };
    playerData.currentPlaylist.empty = function () {
        playerData.isPaused = false;
        playerData.isPlaying = false;
        playerData.currentTrack.unset();
        playerData.tracks = [];
    };

    playerData.dispose = function () {
        this.stop();
        playerData.currentPlaylist.unset();
        playerData.currentPlaylist.empty();
    };

    playerData.isLastTrack = function () {
        if (playerData.tracks.length > 0) {
            if (playerData.currentTrack.index < playerData.tracks.length - 1) {
                return (false);
            } else {
                return (true);
            }
        } else {
            return (true);
        }
    };
    playerData.loadRandomTracks = function (count, callback) {
        playerData.stop();
        playerData.tracks = [];
        playerData.loading = true;
        playerData.currentTrack.unset();
        var d = {
            actualPage: 1,
            resultsPage: count,
            orderBy: "random"
        };
        spieldoseAPI.track.searchTracks("", "", "", false, 1, count, "random", function (response) {
            if (response.ok) {
                if (response.body.tracks && response.body.tracks.length > 0) {
                    playerData.tracks = response.body.tracks;
                    playerData.play();
                    /*
                    let songs = [];
                    for (let i = 0; i < playerData.tracks.length; i++) {
                        songs.push(
                        {
                            "id": playerData.tracks[i].id,
                            "playtimeString": playerData.tracks[i].playtimeString,
                            "name": playerData.tracks[i].title,
                            "artist": playerData.tracks[i].artist,
                            "albumArtist": playerData.tracks[i].albumArtist,
                            "album": playerData.tracks[i].album,
                            "year": playerData.tracks[i].year,
                            "genre": playerData.tracks[i].genre,
                            "url": "/api/track/get/" + playerData.tracks[i].id,
                            "image": playerData.tracks[i].image
                        }
                    );
                    }
                    bus.$emit("setPlayList", songs);
                    */
                }
                playerData.loading = false;
                if (callback && typeof callback === "function") {
                    callback();
                }
            } else {
                // TODO: errors
                playerData.loading = false;
                if (callback && typeof callback === "function") {
                    callback();
                }
            }
        });
    };
    playerData.toggleRepeatMode = function () {
        switch (playerData.repeatTracksMode) {
            case "none":
                playerData.repeatTracksMode = "track";
                break;
            case "track":
                playerData.repeatTracksMode = "all";
                break;
            default:
                playerData.repeatTracksMode = "none";
                break;
        }
    };
    playerData.shufflePlayList = function () {
        shuffle(playerData.tracks);
        playerData.playAtIdx(0);
    };
    playerData.playPreviousTrack = function () {
        if (playerData.currentTrack.index > 0) {
            playerData.currentTrack.index--;
            playerData.currentTrack.track = playerData.tracks[playerData.currentTrack.index];
            if (playerData.isPaused) {
                playerData.isPaused = false;
                playerData.isPlaying = true;
            }
        }
    };
    playerData.playNextTrack = function () {
        if (playerData.tracks.length > 0 && playerData.currentTrack.index < playerData.tracks.length - 1) {
            playerData.currentTrack.index++;
            playerData.currentTrack.track = playerData.tracks[playerData.currentTrack.index];
            if (playerData.isPaused) {
                playerData.isPaused = false;
                playerData.isPlaying = true;
            }
        }
    };
    playerData.play = function () {
        if (playerData.tracks.length > 0) {
            playerData.currentTrack.track = playerData.tracks[playerData.currentTrack.index];
            playerData.isPlaying = true;
            playerData.isPaused = false;
            playerData.isStopped = false;
        }
    };
    playerData.playAtIdx = function (idx) {
        if (playerData.tracks.length > 0 && idx < playerData.tracks.length) {
            playerData.currentTrack.index = idx;
            playerData.currentTrack.track = playerData.tracks[playerData.currentTrack.index];
            playerData.isPlaying = true;
            playerData.isPaused = false;
            playerData.isStopped = false;
        }
    };
    playerData.moveUpIdx = function (idx) {
        if (playerData.tracks.length > 0 && idx > 0) {
            var tmpTrack = playerData.tracks[idx - 1];
            playerData.tracks.splice(idx - 1, 1);
            playerData.tracks.splice(idx, 0, tmpTrack);
        }
    };
    playerData.moveDownIdx = function (idx) {
        if (playerData.tracks.length > 0 && idx < playerData.tracks.length - 1) {
            var tmpTrack = playerData.tracks[idx];
            playerData.tracks.splice(idx, 1);
            playerData.tracks.splice(idx + 1, 0, tmpTrack);
        }
    };
    playerData.removeAtIdx = function (idx) {
        if (playerData.tracks.length > 0 && idx < playerData.tracks.length) {
            if (idx == playerData.currentTrack.index && (playerData.isPlaying || playerData.isPaused)) {
                playerData.playNextTrack();
            }
            playerData.tracks.splice(idx, 1);
        }
    };
    playerData.pause = function () {
        if (playerData.isPlaying) {
            playerData.isPaused = true;
            playerData.isPlaying = false;
            playerData.isStopped = false;
        }
    };
    playerData.resume = function () {
        if (playerData.isPaused) {
            playerData.isPaused = false;
            playerData.isPlaying = true;
            playerData.isStopped = false;
        }
    };
    playerData.stop = function () {
        if (playerData.isPlaying || playerData.isPaused) {
            playerData.isPaused = false;
            playerData.isPlaying = false;
            playerData.isStopped = true;
        }
    };
    playerData.advancePlayList = function () {
        if (playerData.tracks.length > 0 && playerData.currentTrack.index < playerData.tracks.length - 1) {
            playerData.playAtIdx(playerData.currentTrack.index + 1);
        } else {
            playerData.stop();
        }
    };
    return (playerData);
});

const sharedPlayerData = getPlayerData();