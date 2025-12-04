<?php

declare(strict_types=1);

namespace Spieldose;

class CurrentPlaylist
{
    public ?string $id;

    public $ctime;

    public $mtime;

    public int $currentIndex = -1;

    public array $shuffledIndexes = [];

    public object $radioStation;

    public object $playlist;

    public array $tracks = [];

    protected int $totalTracks;

    public function __construct()
    {
        $this->id = \Spieldose\UserSession::isLogged() ? \Spieldose\UserSession::getUserId() : null;
        $this->radioStation = (object) ["id" => null, "name" => null, "url" => null, "playlist" => null, "directStream" => null, "images" => ["small" => null, "normal" => null]];
        $this->playlist = (object) ["id" => null, "name" => null, "public" => false, "owner" => ["id" => null, "name" => null], "allowUpdate" => false];
    }

    private function getTracks(\aportela\DatabaseWrapper\DB $db): array
    {
        $filter = [
            "currentPlaylistId" => \Spieldose\UserSession::getUserId(),
        ];
        $sort = new \aportela\DatabaseBrowserWrapper\Sort(
            [
                new \aportela\DatabaseBrowserWrapper\SortItem("currentPlaylistTrackIndex", \aportela\DatabaseBrowserWrapper\Order::ASC, true),
            ]
        );
        $pager = new \aportela\DatabaseBrowserWrapper\Pager(false, 1, 0);
        $browserResults = \Spieldose\Entities\Track::search($db, $filter, $sort, $pager);
        return ($browserResults->items);
    }

    private function setCurrentTrackIndex(\aportela\DatabaseWrapper\DB $db, int $index): void
    {
        $this->currentIndex = $index;
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
            new \aportela\DatabaseWrapper\Param\IntegerParam(":index", $this->currentIndex),
        ];
        $query = "
                INSERT INTO CURRENT_PLAYLIST
                    (id, ctime, mtime, current_index, radiostation_id)
                VALUES
                    (:id, strftime('%s', 'now'), strftime('%s', 'now'), :index, NULL)
                ON CONFLICT(id) DO
                    UPDATE SET mtime = strftime('%s', 'now'), current_index = :index, radiostation_id = NULL
            ";
        $db->execute($query, $params);
    }

    public function get(\aportela\DatabaseWrapper\DB $db): void
    {
        $this->tracks = [];
        $this->totalTracks = 0;
        if (\Spieldose\UserSession::isLogged()) {
            $params = [];
            $query = "
                SELECT CP.id, CP.ctime, CP.mtime, CP.current_index, CP.radiostation_id, CP.playlist_id, P.name AS playlist_name, P.public, P.user_id AS ownerId, U.name AS ownerName
                FROM CURRENT_PLAYLIST CP
                LEFT JOIN PLAYLIST P ON P.ID = CP.playlist_id
                LEFT JOIN USER U ON U.id = P.user_id
                WHERE CP.id = :id
                ORDER BY CP.current_index
            ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":id", \Spieldose\UserSession::getUserId());
            $data = $db->query($query, $params);
            if (count($data) === 1) {
                $this->id = $data[0]->id;
                $this->ctime = $data[0]->ctime;
                $this->mtime = $data[0]->mtime;
                $this->currentIndex = $data[0]->current_index;
                if (!empty($data[0]->radiostation_id)) {
                    $radioStations = include __DIR__ . "/../Spieldose/RadioStations.php";
                    foreach ($radioStations as $radioStation) {
                        if ($radioStation["id"] == $data[0]->radiostation_id) {
                            $this->radioStation = (object) $radioStation;
                        }
                    }
                }

                if (!empty($data[0]->playlist_id)) {
                    $this->playlist->id = $data[0]->playlist_id;
                    $this->playlist->name = $data[0]->playlist_name;
                    $this->playlist->public = $data[0]->public == "S";
                    $this->playlist->owner = new \stdClass();
                    $this->playlist->owner->id = $data[0]->ownerId;
                    $this->playlist->owner->name = $data[0]->ownerName;
                    $this->playlist->allowUpdate = $data[0]->ownerId == \Spieldose\UserSession::getUserId();
                }

                $query = " SELECT track_shuffled_index FROM CURRENT_PLAYLIST_TRACK WHERE playlist_id = :id ORDER BY track_index ";
                $data = $db->query($query, $params);
                if ($data !== []) {
                    foreach ($data as $item) {
                        $this->shuffledIndexes[] = $item->track_shuffled_index;
                    }

                    $this->tracks = $this->getTracks($db);
                    $this->totalTracks = is_array($this->tracks) ? count($this->tracks) : 0;
                }
            } else {
                $this->id = null;
                $this->ctime = null;
                $this->mtime  = null;
                $this->currentIndex = -1;
                $this->tracks = [];
            }
        } else {
            throw new \Spieldose\Exception\UnauthorizedException("");
        }
    }

    private function AllowSkipPrevious(): bool
    {
        return ($this->totalTracks > 0 && $this->currentIndex > 0);
    }

    private function AllowSkipNext(): bool
    {
        return ($this->totalTracks > 0 && $this->currentIndex < $this->totalTracks);
    }

    public function getCurrentElement(\aportela\DatabaseWrapper\DB $db, bool $shuffled = false): object
    {
        $this->get($db);
        $track = null;
        $radioStation = null;
        $playlist = null;
        if (!empty($this->playlist->id)) {
            $playlist = $this->playlist;
        }

        if (!empty($this->radioStation->id)) {
            $radioStation = $this->radioStation;
        } elseif ($this->currentIndex >= 0 && $this->currentIndex < $this->totalTracks) {
            $track = $shuffled ? $this->tracks[$this->shuffledIndexes[$this->currentIndex]] : $this->tracks[$this->currentIndex];
        }

        return ((object) ["currentTrackIndex" => $this->currentIndex, "currentTrackShuffledIndex" => $this->currentIndex >= 0 ? $this->shuffledIndexes[$this->currentIndex] : -1, "totalTracks" => $this->totalTracks, "currentTrack" => $track, "radioStation" => $radioStation, "playlist" => $playlist]);
    }

    public function getPreviousElement(\aportela\DatabaseWrapper\DB $db, bool $shuffled = false): object
    {
        $this->get($db);
        $track = null;
        $playlist = null;
        if (!empty($this->playlist->id)) {
            $playlist = $this->playlist;
        }

        if ($this->AllowSkipPrevious()) {
            $this->setCurrentTrackIndex($db, $this->currentIndex - 1);
            $track = $shuffled ? $this->tracks[$this->shuffledIndexes[$this->currentIndex]] : $this->tracks[$this->currentIndex];
        }

        return ((object) ["currentTrackIndex" => $this->currentIndex, "currentTrackShuffledIndex" => $this->currentIndex >= 0 ? $this->shuffledIndexes[$this->currentIndex] : -1, "totalTracks" => $this->totalTracks, "currentTrack" => $track, "radioStation" => null, "playlist" => $playlist]);
    }

    public function getNextElement(\aportela\DatabaseWrapper\DB $db, bool $shuffled = false): object
    {
        $this->get($db);
        $track = null;
        $playlist = null;
        if (!empty($this->playlist->id)) {
            $playlist = $this->playlist;
        }

        if ($this->AllowSkipNext()) {
            $this->setCurrentTrackIndex($db, $this->currentIndex + 1);
            $track = $shuffled ? $this->tracks[$this->shuffledIndexes[$this->currentIndex]] : $this->tracks[$this->currentIndex];
        }

        return ((object) ["currentTrackIndex" => $this->currentIndex, "currentTrackShuffledIndex" => $this->currentIndex >= 0 ? $this->shuffledIndexes[$this->currentIndex] : -1, "totalTracks" => $this->totalTracks, "currentTrack" => $track, "radioStation" => null, "playlist" => $playlist]);
    }

    public function getElementAtIndex(\aportela\DatabaseWrapper\DB $db, int $index): object
    {
        $this->get($db);
        $track = null;
        $playlist = null;
        if (!empty($this->playlist->id)) {
            $playlist = $this->playlist;
        }

        if ($index >= 0 && $index < $this->totalTracks) {
            $this->setCurrentTrackIndex($db, $index);
            $track = $this->tracks[$this->currentIndex];
        }

        return ((object) ["currentTrackIndex" => $this->currentIndex, "currentTrackShuffledIndex" => $this->currentIndex >= 0 ? $this->shuffledIndexes[$this->currentIndex] : -1, "totalTracks" => $this->totalTracks, "currentTrack" => $track, "radioStation" => null, "playlist" => $playlist]);
    }

    public function save(\aportela\DatabaseWrapper\DB $db, array $trackIds = []): bool
    {
        if (\Spieldose\UserSession::isLogged()) {
            $this->id = \Spieldose\UserSession::getUserId();
            $totalTracks = count($trackIds);
            $params = [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_index", $totalTracks > 0 ? 0 : -1),
            ];
            if (!empty($this->playlist->id) && $this->playlist->id != \Spieldose\Playlist::FAVORITE_TRACKS_PLAYLIST_ID) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->playlist->id);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":playlist_id");
            }

            $query = "
                INSERT INTO CURRENT_PLAYLIST
                    (id, ctime, mtime, current_index, radiostation_id, playlist_id)
                VALUES
                    (:id, strftime('%s', 'now'), strftime('%s', 'now'), :current_index, NULL, :playlist_id)
                ON CONFLICT(id) DO
                    UPDATE SET mtime = strftime('%s', 'now'), current_index = :current_index, radiostation_id = NULL, playlist_id = :playlist_id
            ";
            $success = false;
            $db->beginTransaction();
            try {
                $db->execute($query, $params);
                $params = [
                    new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                ];
                $db->execute(" DELETE FROM CURRENT_PLAYLIST_TRACK WHERE playlist_id = :playlist_id ", $params);
                if ($totalTracks > 0) {
                    $shuffledIndexes = range(0, $totalTracks - 1);
                    shuffle($shuffledIndexes);
                    foreach ($trackIds as $index => $trackId) {
                        $params = [
                            new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                            new \aportela\DatabaseWrapper\Param\StringParam(":track_id", $trackId),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":track_index", $index),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":track_shuffled_index", $shuffledIndexes[$index]),
                        ];
                        $db->execute(" INSERT INTO CURRENT_PLAYLIST_TRACK (playlist_id, track_id, track_index, track_shuffled_index) VALUES(:playlist_id, :track_id, :track_index, :track_shuffled_index) ", $params);
                    }
                }
                return (true);
            } finally {
                if ($success) {
                    $db->commit();
                } else {
                    $db->rollBack();
                }
            }
        } else {
            throw new \Spieldose\Exception\UnauthorizedException("");
        }
    }

    public function setRadiostation(\aportela\DatabaseWrapper\DB $db, string $id): void
    {
        $this->id = \Spieldose\UserSession::getUserId();
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
            new \aportela\DatabaseWrapper\Param\IntegerParam(":index", $this->currentIndex),
            new \aportela\DatabaseWrapper\Param\StringParam(":radiostation_id", $id),
        ];
        $query = "
                INSERT INTO CURRENT_PLAYLIST
                    (id, ctime, mtime, current_index, radiostation_id, playlist_id)
                VALUES
                    (:id, strftime('%s', 'now'), strftime('%s', 'now'), :index, :radiostation_id, NULL)
                ON CONFLICT(id) DO
                    UPDATE SET mtime = strftime('%s', 'now'), radiostation_id = :radiostation_id
            ";
        $db->execute($query, $params);
    }

    public function setLinkedPlaylist(\aportela\DatabaseWrapper\DB $db, string $id): void
    {
        $this->id = \Spieldose\UserSession::getUserId();
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
            new \aportela\DatabaseWrapper\Param\IntegerParam(":index", $this->currentIndex),
            new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $id),
        ];
        $query = "
                INSERT INTO CURRENT_PLAYLIST
                    (id, ctime, mtime, current_index, radiostation_id, playlist_id)
                VALUES
                    (:id, strftime('%s', 'now'), strftime('%s', 'now'), :index, NULL, :playlist_id)
                ON CONFLICT(id) DO
                    UPDATE SET mtime = strftime('%s', 'now'), playlist_id = :playlist_id
            ";
        $db->execute($query, $params);
    }

    public function append(\aportela\DatabaseWrapper\DB $db, array $trackIds = []): bool
    {
        if (\Spieldose\UserSession::isLogged()) {
            $this->id = \Spieldose\UserSession::getUserId();
            $params = [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
            ];
            $query = "
                INSERT INTO CURRENT_PLAYLIST
                    (id, ctime, mtime, current_index, radiostation_id, playlist_id)
                VALUES
                    (:id, strftime('%s', 'now'), strftime('%s', 'now'), 0, NULL, NULL)
                ON CONFLICT(id) DO
                    UPDATE SET mtime = strftime('%s', 'now')
            ";
            $success = false;
            $db->beginTransaction();
            try {
                $db->execute($query, $params);
                $params = [
                    new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                ];
                if ($trackIds !== []) {
                    $query = " SELECT track_id AS id FROM CURRENT_PLAYLIST_TRACK WHERE playlist_id = :playlist_id ORDER BY track_index ";
                    $data = $db->query($query, $params);
                    if ($data !== []) {
                        $existingTrackIds = [];
                        foreach ($data as $track) {
                            $existingTrackIds[] = $track->id;
                        }
                    }

                    $db->execute(" DELETE FROM CURRENT_PLAYLIST_TRACK WHERE playlist_id = :playlist_id ", $params);

                    $newTrackIds = array_merge($existingTrackIds, $trackIds);
                    $shuffledIndexes = range(0, count($newTrackIds) - 1);
                    shuffle($shuffledIndexes);
                    foreach ($newTrackIds as $index => $trackId) {
                        $params = [
                            new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                            new \aportela\DatabaseWrapper\Param\StringParam(":track_id", $trackId),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":track_index", $index),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":track_shuffled_index", $shuffledIndexes[$index]),
                        ];
                        $db->execute(" INSERT INTO CURRENT_PLAYLIST_TRACK (playlist_id, track_id, track_index, track_shuffled_index) VALUES(:playlist_id, :track_id, :track_index, :track_shuffled_index) ", $params);
                    }
                }
                return (true);
            } finally {
                if ($success) {
                    $db->commit();
                } else {
                    $db->rollBack();
                }
            }
        } else {
            throw new \Spieldose\Exception\UnauthorizedException("");
        }
    }

    public function discover(\aportela\DatabaseWrapper\DB $db, int $count = 32, bool $shuffled = false)
    {
        if ($this->save($db, \Spieldose\Entities\Track::getRandomTrackIds($db, $count))) {
            $this->get($db);
            $track = null;
            $radioStation = null;
            $playlist = null;
            if (!empty($this->playlist->id)) {
                $playlist = $this->playlist;
            }

            if (!empty($this->radioStation->id)) {
                $radioStation = $this->radioStation;
            } elseif ($this->currentIndex >= 0 && $this->currentIndex < $this->totalTracks) {
                $track = $shuffled ? $this->tracks[$this->shuffledIndexes[$this->currentIndex]] : $this->tracks[$this->currentIndex];
            }

            return ((object) ["currentTrackIndex" => $this->currentIndex, "currentTrackShuffledIndex" => $this->currentIndex >= 0 ? $this->shuffledIndexes[$this->currentIndex] : -1, "shuffledIndexes" => $this->shuffledIndexes, "totalTracks" => $this->totalTracks, "currentTrack" => $track, "radioStation" => $radioStation, "tracks" => $this->tracks, "playlist" => $playlist]);
        } else {
            // TODO
            throw new \Exception("");
        }
    }


    public function removeElementAtIndex(\aportela\DatabaseWrapper\DB $db, int $index = -1, bool $shuffled = false)
    {
        if ($index >= 0) {
            $this->get($db);
            $trackIds = [];
            foreach ($this->tracks as $track) {
                $trackIds[] = $track->id;
            }

            array_splice($trackIds, $index, 1);

            if ($this->save($db, $trackIds)) {
                $this->get($db);
                $track = null;
                $radioStation = null;
                $playlist = null;
                if (!empty($this->playlist->id)) {
                    $playlist = $this->playlist;
                }

                if (!empty($this->radioStation->id)) {
                    $radioStation = $this->radioStation;
                } elseif ($this->currentIndex >= 0 && $this->currentIndex < $this->totalTracks) {
                    $track = $shuffled ? $this->tracks[$this->shuffledIndexes[$this->currentIndex]] : $this->tracks[$this->currentIndex];
                }

                return ((object) ["currentTrackIndex" => $this->currentIndex, "currentTrackShuffledIndex" => $this->currentIndex >= 0 ? $this->shuffledIndexes[$this->currentIndex] : -1, "shuffledIndexes" => $this->shuffledIndexes, "totalTracks" => $this->totalTracks, "currentTrack" => $track, "radioStation" => $radioStation, "tracks" => $this->tracks, "playlist" => $playlist]);
            } else {
                // TODO
                throw new \Exception("");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("index");
        }
    }


    public function randomSort(\aportela\DatabaseWrapper\DB $db, bool $shuffled = false)
    {
        $this->get($db);
        $playlist = null;
        if (!empty($this->playlist->id)) {
            $playlist = $this->playlist;
        }

        $trackIds = [];
        foreach ($this->tracks as $track) {
            $trackIds[] = $track->id;
        }

        shuffle($trackIds);
        if ($this->save($db, $trackIds)) {
            $this->get($db);
            $track = null;
            $radioStation = null;
            if (!empty($this->radioStation->id)) {
                $radioStation = $this->radioStation;
            } elseif ($this->currentIndex >= 0 && $this->currentIndex < $this->totalTracks) {
                $track = $shuffled ? $this->tracks[$this->shuffledIndexes[$this->currentIndex]] : $this->tracks[$this->currentIndex];
            }

            return ((object) ["currentTrackIndex" => $this->currentIndex, "currentTrackShuffledIndex" => $this->currentIndex >= 0 ? $this->shuffledIndexes[$this->currentIndex] : -1, "shuffledIndexes" => $this->shuffledIndexes, "totalTracks" => $this->totalTracks, "currentTrack" => $track, "radioStation" => $radioStation, "tracks" => $this->tracks, "playlist" => $playlist]);
        } else {
            // TODO
            throw new \Exception("");
        }
    }

    public function sortByIndexes(\aportela\DatabaseWrapper\DB $db, array $indexes = [], bool $shuffled = false)
    {
        $totalIndexes = count($indexes);
        if ($totalIndexes > 0) {
            $this->get($db);
            $playlist = null;
            if (!empty($this->playlist->id)) {
                $playlist = $this->playlist;
            }

            if (count($this->tracks) === $totalIndexes) {
                $trackIds = [];
                for ($i = 0; $i < $totalIndexes; ++$i) {
                    $trackIds[] = $this->tracks[$indexes[$i]]->id;
                }

                if ($this->save($db, $trackIds)) {
                    $this->get($db);
                    $track = null;
                    $radioStation = null;
                    if (!empty($this->radioStation->id)) {
                        $radioStation = $this->radioStation;
                    } elseif ($this->currentIndex >= 0 && $this->currentIndex < $this->totalTracks) {
                        $track = $shuffled ? $this->tracks[$this->shuffledIndexes[$this->currentIndex]] : $this->tracks[$this->currentIndex];
                    }

                    return ((object) ["currentTrackIndex" => $this->currentIndex, "currentTrackShuffledIndex" => $this->currentIndex >= 0 ? $this->shuffledIndexes[$this->currentIndex] : -1, "shuffledIndexes" => $this->shuffledIndexes, "totalTracks" => $this->totalTracks, "currentTrack" => $track, "radioStation" => $radioStation, "tracks" => $this->tracks, "playlist" => $playlist]);
                } else {
                    // TODO
                    throw new \Exception("");
                }
            } else {
                // TODO
                throw new \Exception("");
            }
        }
        return null;
    }
}
