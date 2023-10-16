<?php

declare(strict_types=1);

namespace Spieldose;

class CurrentPlaylist
{
    public ?string $id;
    public $ctime = null;
    public $mtime = null;
    public int $currentIndex;
    public array $shuffledIndexes;
    public object $radioStation;
    public object $playlist;
    public array $tracks = [];
    protected int $totalTracks;

    public function __construct()
    {
        $this->id = \Spieldose\UserSession::isLogged() ? \Spieldose\UserSession::getUserId() : null;
        $this->currentIndex = -1;
        $this->radioStation = (object) ["id" => null, "name" => null, "url" => null, "playlist" => null, "directStream" => null, "images" => ["small" => null, "normal" => null]];
        $this->playlist = (object) ["id" => null, "name" => null];
        $this->tracks = [];
        $this->shuffledIndexes = [];
    }

    public function __destruct()
    {
    }

    private function getTracks(\aportela\DatabaseWrapper\DB $dbh): array
    {
        $filter = array(
            "currentPlaylistId" => \Spieldose\UserSession::getUserId()
        );
        $sort = new \aportela\DatabaseBrowserWrapper\Sort(
            [
                new \aportela\DatabaseBrowserWrapper\SortItem("currentPlaylistTrackIndex", \aportela\DatabaseBrowserWrapper\Order::ASC, true)
            ]
        );
        $pager = new \aportela\DatabaseBrowserWrapper\Pager(false, 1, 0);
        $data = \Spieldose\Entities\Track::search($dbh, $filter, $sort, $pager);
        return ($data->items);
    }

    private function setCurrentTrackIndex(\aportela\DatabaseWrapper\DB $dbh, int $index): void
    {
        $this->currentIndex = $index;
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
            new \aportela\DatabaseWrapper\Param\IntegerParam(":index", $this->currentIndex)
        );
        $query = "
                INSERT INTO CURRENT_PLAYLIST
                    (id, ctime, mtime, current_index, radiostation_id)
                VALUES
                    (:id, strftime('%s', 'now'), strftime('%s', 'now'), :index, NULL)
                ON CONFLICT(id) DO
                    UPDATE SET mtime = strftime('%s', 'now'), current_index = :index, radiostation_id = NULL
            ";
        $dbh->exec($query, $params);
    }

    public function get(\aportela\DatabaseWrapper\DB $dbh): void
    {
        $this->tracks = [];
        $this->totalTracks = 0;
        if (\Spieldose\UserSession::isLogged()) {
            $params = array();
            $query = null;
            $query = "
                SELECT CP.id, CP.ctime, CP.mtime, CP.current_index, CP.radiostation_id, CP.playlist_id, P.name AS playlist_name
                FROM CURRENT_PLAYLIST CP
                LEFT JOIN PLAYLIST P ON P.ID = CP.playlist_id
                WHERE CP.id = :id
            ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":id", \Spieldose\UserSession::getUserId());
            $data = $dbh->query($query, $params);
            if (count($data) == 1) {
                $this->id = $data[0]->id;
                $this->ctime = $data[0]->ctime;
                $this->mtime = $data[0]->mtime;
                $this->currentIndex = $data[0]->current_index;
                if (!empty($data[0]->radiostation_id)) {
                    $radioStations = include "../Spieldose/RadioStations.php";
                    foreach ($radioStations as $radioStation) {
                        if ($radioStation["id"] == $data[0]->radiostation_id) {
                            $this->radioStation = (object)$radioStation;
                        }
                    }
                }
                if (!empty($data[0]->playlist_id)) {
                    $this->playlist->id = $data[0]->playlist_id;
                    $this->playlist->name = $data[0]->playlist_name;
                }
                $query = " SELECT track_shuffled_index FROM CURRENT_PLAYLIST_TRACK WHERE playlist_id = :id ORDER BY track_index ";
                $data = $dbh->query($query, $params);
                if (count($data) > 0) {
                    foreach ($data as $item) {
                        $this->shuffledIndexes[] = $item->track_shuffled_index;
                    }
                    $this->tracks = $this->getTracks($dbh);
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

    public function getCurrentElement(\aportela\DatabaseWrapper\DB $dbh, bool $shuffled = false): object
    {
        $this->get($dbh);
        $track = null;
        $radioStation = null;
        $playlist = null;
        if (!empty($this->playlist->id)) {
            $playlist = $this->playlist;
        }
        if (!empty($this->radioStation->id)) {
            $radioStation = $this->radioStation;
        } else {
            if ($this->currentIndex >= 0 && $this->currentIndex < $this->totalTracks) {
                if (!$shuffled) {
                    $track = $this->tracks[$this->currentIndex];
                } else {
                    $track = $this->tracks[$this->shuffledIndexes[$this->currentIndex]];
                }
            }
        }
        return ((object) ["currentTrackIndex" => $this->currentIndex, "totalTracks" => $this->totalTracks, "currentTrack" => $track, "radioStation" => $radioStation, "playlist" => $playlist]);
    }

    public function getPreviousElement(\aportela\DatabaseWrapper\DB $dbh, bool $shuffled = false): object
    {
        $this->get($dbh);
        $track = null;
        $playlist = null;
        if (!empty($this->playlist->id)) {
            $playlist = $this->playlist;
        }
        if ($this->AllowSkipPrevious()) {
            $this->setCurrentTrackIndex($dbh, $this->currentIndex - 1);
            if (!$shuffled) {
                $track = $this->tracks[$this->currentIndex];
            } else {
                $track = $this->tracks[$this->shuffledIndexes[$this->currentIndex]];
            }
        }
        return ((object) ["currentTrackIndex" => $this->currentIndex, "totalTracks" => $this->totalTracks, "currentTrack" => $track, "radioStation" => null, "playlist" => $playlist]);
    }

    public function getNextElement(\aportela\DatabaseWrapper\DB $dbh, bool $shuffled = false): object
    {
        $this->get($dbh);
        $track = null;
        $playlist = null;
        if (!empty($this->playlist->id)) {
            $playlist = $this->playlist;
        }
        if ($this->AllowSkipNext()) {
            $this->setCurrentTrackIndex($dbh, $this->currentIndex + 1);
            if (!$shuffled) {
                $track = $this->tracks[$this->currentIndex];
            } else {
                $track = $this->tracks[$this->shuffledIndexes[$this->currentIndex]];
            }
        }
        return ((object) ["currentTrackIndex" => $this->currentIndex, "totalTracks" => $this->totalTracks, "currentTrack" => $track, "radioStation" => null, "playlist" => $playlist]);
    }

    public function getElementAtIndex(\aportela\DatabaseWrapper\DB $dbh, int $index): object
    {
        $this->get($dbh);
        $track = null;
        $playlist = null;
        if (!empty($this->playlist->id)) {
            $playlist = $this->playlist;
        }
        if ($index >= 0 && $index < $this->totalTracks) {
            $this->setCurrentTrackIndex($dbh, $index);
            $track = $this->tracks[$this->currentIndex];
        }
        return ((object) ["currentTrackIndex" => $this->currentIndex, "totalTracks" => $this->totalTracks, "currentTrack" => $track, "radioStation" => null, "playlist" => $playlist]);
    }

    public function save(\aportela\DatabaseWrapper\DB $dbh, array $trackIds = []): bool
    {
        if (\Spieldose\UserSession::isLogged()) {
            $this->id = \Spieldose\UserSession::getUserId();
            $totalTracks = count($trackIds);
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_index", $totalTracks > 0 ? 0 : -1)
            );
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
            $dbh->beginTransaction();
            try {
                $dbh->exec($query, $params);
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id)
                );
                $dbh->exec(" DELETE FROM CURRENT_PLAYLIST_TRACK WHERE playlist_id = :playlist_id ", $params);
                if (is_array($trackIds) && $totalTracks > 0) {
                    $shuffledIndexes = range(0, $totalTracks - 1);
                    shuffle($shuffledIndexes);
                    foreach ($trackIds as $index => $trackId) {
                        $params = array(
                            new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                            new \aportela\DatabaseWrapper\Param\StringParam(":track_id", $trackId),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":track_index", $index),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":track_shuffled_index", $shuffledIndexes[$index])
                        );
                        $dbh->exec(" INSERT INTO CURRENT_PLAYLIST_TRACK (playlist_id, track_id, track_index, track_shuffled_index) VALUES(:playlist_id, :track_id, :track_index, :track_shuffled_index) ", $params);
                    }
                }
                $success = true;
                return ($success);
            } finally {
                if ($success) {
                    $dbh->commit();
                } else {
                    $dbh->rollBack();
                }
            }
        } else {
            throw new \Spieldose\Exception\UnauthorizedException("");
        }
    }

    public function setRadiostation(\aportela\DatabaseWrapper\DB $dbh, string $id)
    {
        $this->id = \Spieldose\UserSession::getUserId();
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
            new \aportela\DatabaseWrapper\Param\IntegerParam(":index", $this->currentIndex),
            new \aportela\DatabaseWrapper\Param\StringParam(":radiostation_id", $id)
        );
        $query = "
                INSERT INTO CURRENT_PLAYLIST
                    (id, ctime, mtime, current_index, radiostation_id, playlist_id)
                VALUES
                    (:id, strftime('%s', 'now'), strftime('%s', 'now'), :index, :radiostation_id, NULL)
                ON CONFLICT(id) DO
                    UPDATE SET mtime = strftime('%s', 'now'), radiostation_id = :radiostation_id
            ";
        $dbh->exec($query, $params);
    }

    public function setLinkedPlaylist(\aportela\DatabaseWrapper\DB $dbh, string $id)
    {
        $this->id = \Spieldose\UserSession::getUserId();
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
            new \aportela\DatabaseWrapper\Param\IntegerParam(":index", $this->currentIndex),
            new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $id)
        );
        $query = "
                INSERT INTO CURRENT_PLAYLIST
                    (id, ctime, mtime, current_index, radiostation_id, playlist_id)
                VALUES
                    (:id, strftime('%s', 'now'), strftime('%s', 'now'), :index, NULL, :playlist_id)
                ON CONFLICT(id) DO
                    UPDATE SET mtime = strftime('%s', 'now'), playlist_id = :playlist_id
            ";
        $dbh->exec($query, $params);
    }

    public function append(\aportela\DatabaseWrapper\DB $dbh, array $trackIds = []): bool
    {
        if (\Spieldose\UserSession::isLogged()) {
            $this->id = \Spieldose\UserSession::getUserId();
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
            );
            $query = "
                INSERT INTO CURRENT_PLAYLIST
                    (id, ctime, mtime, current_index, radiostation_id, playlist_id)
                VALUES
                    (:id, strftime('%s', 'now'), strftime('%s', 'now'), 0, NULL, NULL)
                ON CONFLICT(id) DO
                    UPDATE SET mtime = strftime('%s', 'now')
            ";
            $success = false;
            $dbh->beginTransaction();
            try {
                $dbh->exec($query, $params);
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id)
                );
                if (is_array($trackIds) && count($trackIds) > 0) {
                    $query = " SELECT track_id AS id FROM CURRENT_PLAYLIST_TRACK WHERE playlist_id = :playlist_id ORDER BY track_index ";
                    $data = $dbh->query($query, $params);
                    if (count($data) > 0) {
                        $existingTrackIds = [];
                        foreach ($data as $track) {
                            $existingTrackIds[] = $track->id;
                        }
                    }
                    if (count($existingTrackIds) > 0) {
                        $dbh->exec(" DELETE FROM CURRENT_PLAYLIST_TRACK WHERE playlist_id = :playlist_id ", $params);
                    }
                    $newTrackIds = array_merge($existingTrackIds, $trackIds);
                    $shuffledIndexes = range(0, count($newTrackIds) - 1);
                    shuffle($shuffledIndexes);
                    foreach ($newTrackIds as $index => $trackId) {
                        $params = array(
                            new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                            new \aportela\DatabaseWrapper\Param\StringParam(":track_id", $trackId),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":track_index", $index),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":track_shuffled_index", $shuffledIndexes[$index])
                        );
                        $dbh->exec(" INSERT INTO CURRENT_PLAYLIST_TRACK (playlist_id, track_id, track_index, track_shuffled_index) VALUES(:playlist_id, :track_id, :track_index, :track_shuffled_index) ", $params);
                    }
                }
                $success = true;
                return ($success);
            } finally {
                if ($success) {
                    $dbh->commit();
                } else {
                    $dbh->rollBack();
                }
            }
        } else {
            throw new \Spieldose\Exception\UnauthorizedException("");
        }
    }

    public function discover(\aportela\DatabaseWrapper\DB $dbh, int $count = 32, bool $shuffled = false)
    {
        if ($this->save($dbh, \Spieldose\Entities\Track::getRandomTrackIds($dbh, $count))) {
            $this->get($dbh);
            $track = null;
            $radioStation = null;
            $playlist = null;
            if (!empty($this->playlist->id)) {
                $playlist = $this->playlist;
            }
            if (!empty($this->radioStation->id)) {
                $radioStation = $this->radioStation;
            } else {
                if ($this->currentIndex >= 0 && $this->currentIndex < $this->totalTracks) {
                    if (!$shuffled) {
                        $track = $this->tracks[$this->currentIndex];
                    } else {
                        $track = $this->tracks[$this->shuffledIndexes[$this->currentIndex]];
                    }
                }
            }
            return ((object) ["currentTrackIndex" => $this->currentIndex, "totalTracks" => $this->totalTracks, "currentTrack" => $track, "radioStation" => $radioStation, "tracks" => $this->tracks, "playlist" => $playlist]);
        } else {
            // TODO
            throw new \Exception("");
        }
    }


    public function removeElementAtIndex(\aportela\DatabaseWrapper\DB $dbh, int $index = -1, bool $shuffled = false)
    {
        if ($index >= 0) {
            $this->get($dbh);
            $trackIds = [];
            foreach ($this->tracks as $track) {
                $trackIds[] = $track->id;
            }
            array_splice($trackIds, $index, 1);

            if ($this->save($dbh, $trackIds)) {
                $this->get($dbh);
                $track = null;
                $radioStation = null;
                $playlist = null;
                if (!empty($this->playlist->id)) {
                    $playlist = $this->playlist;
                }
                if (!empty($this->radioStation->id)) {
                    $radioStation = $this->radioStation;
                } else {
                    if ($this->currentIndex >= 0 && $this->currentIndex < $this->totalTracks) {
                        if (!$shuffled) {
                            $track = $this->tracks[$this->currentIndex];
                        } else {
                            $track = $this->tracks[$this->shuffledIndexes[$this->currentIndex]];
                        }
                    }
                }
                return ((object) ["currentTrackIndex" => $this->currentIndex, "totalTracks" => $this->totalTracks, "currentTrack" => $track, "radioStation" => $radioStation, "tracks" => $this->tracks, "playlist" => $playlist]);
            } else {
                // TODO
                throw new \Exception("");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("index");
        }
    }


    public function randomSort(\aportela\DatabaseWrapper\DB $dbh, bool $shuffled = false)
    {
        $this->get($dbh);
        $playlist = null;
        if (!empty($this->playlist->id)) {
            $playlist = $this->playlist;
        }
        $trackIds = [];
        foreach ($this->tracks as $track) {
            $trackIds[] = $track->id;
        }
        shuffle($trackIds);
        if ($this->save($dbh, $trackIds)) {
            $this->get($dbh);
            $track = null;
            $radioStation = null;
            if (!empty($this->radioStation->id)) {
                $radioStation = $this->radioStation;
            } else {
                if ($this->currentIndex >= 0 && $this->currentIndex < $this->totalTracks) {
                    if (!$shuffled) {
                        $track = $this->tracks[$this->currentIndex];
                    } else {
                        $track = $this->tracks[$this->shuffledIndexes[$this->currentIndex]];
                    }
                }
            }
            return ((object) ["currentTrackIndex" => $this->currentIndex, "totalTracks" => $this->totalTracks, "currentTrack" => $track, "radioStation" => $radioStation, "tracks" => $this->tracks, "playlist" => $playlist]);
        } else {
            // TODO
            throw new \Exception("");
        }
    }

    public function sortByIndexes(\aportela\DatabaseWrapper\DB $dbh, array $indexes = [], bool $shuffled = false)
    {
        $totalIndexes = count($indexes);
        if ($totalIndexes > 0) {
            $this->get($dbh);
            $playlist = null;
            if (!empty($this->playlist->id)) {
                $playlist = $this->playlist;
            }
            if (count($this->tracks) == $totalIndexes) {
                $trackIds = [];
                for ($i = 0; $i < $totalIndexes; $i++) {
                    $trackIds[] = $this->tracks[$indexes[$i]]->id;
                }
                if ($this->save($dbh, $trackIds)) {
                    $this->get($dbh);
                    $track = null;
                    $radioStation = null;
                    if (!empty($this->radioStation->id)) {
                        $radioStation = $this->radioStation;
                    } else {
                        if ($this->currentIndex >= 0 && $this->currentIndex < $this->totalTracks) {
                            if (!$shuffled) {
                                $track = $this->tracks[$this->currentIndex];
                            } else {
                                $track = $this->tracks[$this->shuffledIndexes[$this->currentIndex]];
                            }
                        }
                    }
                    return ((object) ["currentTrackIndex" => $this->currentIndex, "totalTracks" => $this->totalTracks, "currentTrack" => $track, "radioStation" => $radioStation, "tracks" => $this->tracks, "playlist" => $playlist]);
                } else {
                    // TODO
                    throw new \Exception("");
                }
            } else {
                // TODO
                throw new \Exception("");
            }
        }
    }
}
