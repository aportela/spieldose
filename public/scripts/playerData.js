import { default as spieldoseAPI } from './api.js';

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

    playerData.download = function (trackId) {
        if (trackId) {
            window.location = "api/track/get/" + trackId;
        }
    };
    playerData.love = function (track) {
        playerData.loading = true;
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
        playerData.loading = true;
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
        playerData.currentTrack.index = 0;
        playerData.currentTrack.track = null;
    };
    playerData.currentTrack.download = function () {
        if (playerData.currentTrack.track) {
            playerData.download(playerData.currentTrack.track.id);
        }
    };
    playerData.currentTrack.setLoved = function () {
        if (playerData.currentTrack.track) {
            playerData.love(playerData.currentTrack.track);
        }
    };
    playerData.currentTrack.unSetLoved = function () {
        if (playerData.currentTrack.track) {
            playerData.unlove(playerData.currentTrack.track);
        }
    };

    playerData.currentPlaylist.unset = function () {
        playerData.currentPlaylist.id = null;
        playerData.currentPlaylist.name = null;
    };
    playerData.currentPlaylist.empty = function () {
        playerData.currentTrack.unset();
        playerData.tracks = [];
    };
    playerData.currentPlaylist.replace = function (tracks) {
        playerData.currentPlaylist.empty();
        playerData.tracks = tracks;
        playerData.playback.play();
    };
    playerData.currentPlaylist.enqueue = function (tracks) {
        if (tracks.length > 0) {
            for (var i = 0; i < tracks.length; i++) {
                playerData.tracks.push(tracks[i]);
            }
        }
    };
    playerData.currentPlaylist.isSet = function () {
        return (playerData.currentPlaylist.id ? true : false);
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
    playerData.currentPlaylist.loadRandomTracks = function (count, callback) {
        playerData.playback.stop();
        playerData.tracks = [];
        playerData.loading = true;
        playerData.currentTrack.unset();
        spieldoseAPI.track.searchTracks("", "", "", false, 1, count, "random", function (response) {
            if (response.ok) {
                if (response.body.tracks && response.body.tracks.length > 0) {
                    playerData.tracks = response.body.tracks;
                }
                playerData.loading = false;
                playerData.playback.play();
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
    playerData.currentPlaylist.playAtIdx = function (idx) {
        if (playerData.tracks.length > 0 && idx < playerData.tracks.length) {
            playerData.currentTrack.index = idx;
            playerData.currentTrack.track = playerData.tracks[playerData.currentTrack.index];
            playerData.isPlaying = true;
            playerData.isPaused = false;
            playerData.isStopped = false;
        }
    };
    playerData.currentPlaylist.shuffle = function () {
        shuffle(playerData.tracks);
        playerData.currentPlaylist.playAtIdx(0);
    };
    playerData.currentPlaylist.moveItemUp = function (idx) {
        if (playerData.tracks.length > 0 && idx > 0) {
            var tmpTrack = playerData.tracks[idx - 1];
            playerData.tracks.splice(idx - 1, 1);
            playerData.tracks.splice(idx, 0, tmpTrack);
        }
    };
    playerData.currentPlaylist.moveItemDown = function (idx) {
        if (playerData.tracks.length > 0 && idx < playerData.tracks.length - 1) {
            var tmpTrack = playerData.tracks[idx];
            playerData.tracks.splice(idx, 1);
            playerData.tracks.splice(idx + 1, 0, tmpTrack);
        }
    };
    playerData.currentPlaylist.removeItem = function (idx) {
        if (playerData.tracks.length > 0 && idx < playerData.tracks.length) {
            if (idx == playerData.currentTrack.index && (playerData.isPlaying || playerData.isPaused)) {
                playerData.currentPlaylist.playNext();
            }
            playerData.tracks.splice(idx, 1);
        }
    };
    playerData.currentPlaylist.playPrevious = function () {
        if (playerData.currentTrack.index > 0) {
            playerData.currentTrack.index--;
            playerData.currentTrack.track = playerData.tracks[playerData.currentTrack.index];
            if (playerData.isPaused) {
                playerData.isPaused = false;
                playerData.isPlaying = true;
            }
        }
    };
    playerData.currentPlaylist.playNext = function () {
        if (playerData.tracks.length > 0) {
            if (playerData.repeatTracksMode != "track") {
                if (playerData.currentTrack.index < playerData.tracks.length - 1) {
                    playerData.currentTrack.index++;
                    playerData.currentTrack.track = playerData.tracks[playerData.currentTrack.index];
                    if (playerData.isPaused) {
                        playerData.isPaused = false;
                        playerData.isPlaying = true;
                    }
                } else if (playerData.repeatTracksMode == "all") {
                    playerData.currentTrack.index = 0;
                    playerData.currentTrack.track = playerData.tracks[playerData.currentTrack.index];
                    if (playerData.isPaused) {
                        playerData.isPaused = false;
                        playerData.isPlaying = true;
                    }
                }
            }
        }
    };

    playerData.playback.play = function () {
        if (playerData.tracks.length > 0) {
            playerData.currentTrack.track = playerData.tracks[playerData.currentTrack.index];
            playerData.isPlaying = true;
            playerData.isPaused = false;
            playerData.isStopped = false;
        }
    };
    playerData.playback.stop = function () {
        playerData.isPaused = false;
        playerData.isPlaying = false;
        playerData.isStopped = true;
    };
    playerData.playback.pause = function () {
        if (playerData.isPlaying) {
            playerData.isPaused = true;
            playerData.isPlaying = false;
            playerData.isStopped = false;
        }
    };
    playerData.playback.resume = function () {
        if (playerData.isPaused) {
            playerData.isPaused = false;
            playerData.isPlaying = true;
            playerData.isStopped = false;
        }
    };
    playerData.playback.toggleRepeatMode = function () {
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

    playerData.dispose = function () {
        playerData.playback.stop();
        playerData.currentPlaylist.unset();
        playerData.currentPlaylist.empty();
    };

    return (playerData);
});

const sharedPlayerData = getPlayerData();

export default sharedPlayerData;