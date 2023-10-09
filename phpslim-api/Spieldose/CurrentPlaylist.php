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
    public array $tracks = [];
    protected int $totalTracks;

    public function __construct()
    {
        $this->id = \Spieldose\UserSession::isLogged() ? \Spieldose\UserSession::getUserId() : null;
        $this->currentIndex = -1;
        $this->radioStation = (object) ["id" => null, "name" => null];
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
                SELECT CP.id, CP.ctime, CP.mtime, CP.current_index, CP.radiostation_id, RS.name
                FROM CURRENT_PLAYLIST CP
                LEFT JOIN RADIO_STATION RS ON RS.ID = CP.radiostation_id
                WHERE CP.id = :id
            ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":id", \Spieldose\UserSession::getUserId());
            $data = $dbh->query($query, $params);
            if (count($data) == 1) {
                $this->id = $data[0]->id;
                $this->ctime = $data[0]->ctime;
                $this->mtime = $data[0]->mtime;
                $this->currentIndex = $data[0]->current_index;
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

    public static function getCurrentElement(\aportela\DatabaseWrapper\DB $dbh, bool $shuffled = false): object
    {
        $currentPlaylist = new \Spieldose\CurrentPlaylist();
        $currentPlaylist->get($dbh);
        $track = null;
        $radioStation = null;
        if (!empty($currentPlaylist->radioStation->id)) {
            $radioStation = $currentPlaylist->radioStation;
        } else {
            if ($currentPlaylist->currentIndex >= 0 && $currentPlaylist->currentIndex < $currentPlaylist->totalTracks) {
                if (!$shuffled) {
                    $track = $currentPlaylist->tracks[$currentPlaylist->currentIndex];
                } else {
                    $track = $currentPlaylist->tracks[$currentPlaylist->shuffledIndexes[$currentPlaylist->currentIndex]];
                }
            }
        }
        return ((object) ["currentTrackIndex" => $currentPlaylist->currentIndex, "totalTracks" => $currentPlaylist->totalTracks, "track" => $track, "radioStation" => $radioStation]);
    }

    public static function getPreviousElement(\aportela\DatabaseWrapper\DB $dbh, bool $shuffled = false): object
    {
        $currentPlaylist = new \Spieldose\CurrentPlaylist();
        $currentPlaylist->get($dbh);
        $track = null;
        if ($currentPlaylist->AllowSkipPrevious()) {
            $currentPlaylist->setCurrentTrackIndex($dbh, $currentPlaylist->currentIndex - 1);
            if (!$shuffled) {
                $track = $currentPlaylist->tracks[$currentPlaylist->currentIndex];
            } else {
                $track = $currentPlaylist->tracks[$currentPlaylist->shuffledIndexes[$currentPlaylist->currentIndex]];
            }
        }
        return ((object) ["currentTrackIndex" => $currentPlaylist->currentIndex, "totalTracks" => $currentPlaylist->totalTracks, "track" => $track, "radioStation" => null]);
    }

    public static function getNextElement(\aportela\DatabaseWrapper\DB $dbh, bool $shuffled = false): object
    {
        $currentPlaylist = new \Spieldose\CurrentPlaylist();
        $currentPlaylist->get($dbh);
        $track = null;
        if ($currentPlaylist->AllowSkipNext()) {
            $currentPlaylist->setCurrentTrackIndex($dbh, $currentPlaylist->currentIndex + 1);
            if (!$shuffled) {
                $track = $currentPlaylist->tracks[$currentPlaylist->currentIndex];
            } else {
                $track = $currentPlaylist->tracks[$currentPlaylist->shuffledIndexes[$currentPlaylist->currentIndex]];
            }
        }
        return ((object) ["currentTrackIndex" => $currentPlaylist->currentIndex, "totalTracks" => $currentPlaylist->totalTracks, "track" => $track, "radioStation" => null]);
    }

    public static function getElementAtIndex(\aportela\DatabaseWrapper\DB $dbh, int $index): object
    {
        $currentPlaylist = new \Spieldose\CurrentPlaylist();
        $currentPlaylist->get($dbh);
        $track = null;
        if ($index >= 0 && $index < $currentPlaylist->totalTracks) {
            $currentPlaylist->setCurrentTrackIndex($dbh, $index);
            $track = $currentPlaylist->tracks[$currentPlaylist->currentIndex];
        }
        return ((object) ["currentTrackIndex" => $currentPlaylist->currentIndex, "totalTracks" => $currentPlaylist->totalTracks, "track" => $track, "radioStation" => null]);
    }

    public function save(\aportela\DatabaseWrapper\DB $dbh, array $trackIds = []): bool
    {
        if (\Spieldose\UserSession::isLogged()) {
            $this->id = \Spieldose\UserSession::getUserId();
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_index", count($trackIds) > 0 ? 0 : -1)
            );
            if (!empty($this->radioStation->id)) {
                //$params[] = new \aportela\DatabaseWrapper\Param\StringParam(":radiostation_id", $this->radioStation->id);
            } else {
                //$params[] = new \aportela\DatabaseWrapper\Param\NullParam(":radiostation_id");
            }
            $query = "
                INSERT INTO CURRENT_PLAYLIST
                    (id, ctime, mtime, current_index, radiostation_id)
                VALUES
                    (:id, strftime('%s', 'now'), strftime('%s', 'now'), :current_index, NULL)
                ON CONFLICT(id) DO
                    UPDATE SET mtime = strftime('%s', 'now'), current_index = :current_index, radiostation_id = NULL
            ";
            $success = false;
            $dbh->beginTransaction();
            try {
                $dbh->exec($query, $params);
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id)
                );
                $dbh->exec(" DELETE FROM CURRENT_PLAYLIST_TRACK WHERE playlist_id = :playlist_id ", $params);
                if (is_array($trackIds) && count($trackIds) > 0) {
                    $shuffledIndexes = range(0, count($trackIds) - 1);
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

    public function append(\aportela\DatabaseWrapper\DB $dbh, array $trackIds = []): bool
    {
        if (\Spieldose\UserSession::isLogged()) {
            $this->id = \Spieldose\UserSession::getUserId();
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
            );
            if (!empty($this->radioStation->id)) {
                //$params[] = new \aportela\DatabaseWrapper\Param\StringParam(":radiostation_id", $this->radioStation->id);
            } else {
                //$params[] = new \aportela\DatabaseWrapper\Param\NullParam(":radiostation_id");
            }
            $query = "
                INSERT INTO CURRENT_PLAYLIST
                    (id, ctime, mtime, current_index, radiostation_id)
                VALUES
                    (:id, strftime('%s', 'now'), strftime('%s', 'now'), 0, NULL)
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
}
