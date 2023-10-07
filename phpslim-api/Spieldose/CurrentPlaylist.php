<?php

declare(strict_types=1);

namespace Spieldose;

class CurrentPlaylist
{
    public string $id;
    public $ctime = null;
    public $mtime = null;
    public int $currentIndex;
    public ?object $radioStation;
    public array $tracks = [];

    public function __construct(string $id, array $tracks = [], int $currentIndex)
    {
        $this->id = \Spieldose\UserSession::isLogged() ? \Spieldose\UserSession::getUserId() : null;
        $this->tracks = $tracks;
    }

    public function __destruct()
    {
    }

    public function get(\aportela\DatabaseWrapper\DB $dbh): void
    {
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
                $this->tracks = [];
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

    public function save(\aportela\DatabaseWrapper\DB $dbh)
    {
        if (\Spieldose\UserSession::isLogged()) {
            if (empty($this->id)) {
                $this->id = \Spieldose\UserSession::getUserId();
            }
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_index", $this->currentIndex)
            );
            if (!empty($this->radioStation->id)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":radiostation_id", $this->radioStation->id);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":radiostation_id");
            }
            $query = "
                INSERT INTO CURRENT_PLAYLIST
                    (id, ctime, mtime, current_index, radiostation_id)
                VALUES
                    (:id, strftime('%s', 'now'), strftime('%s', 'now'), :current_index, :radiostation_id)
                ON CONFLICT(id) DO
                    UPDATE SET mtime = strftime('%s', 'now'), current_index = :current_index, radiostation_id = :radiostation_id
            ";
            $dbh->exec($query, $params);
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", \Spieldose\UserSession::getUserId())
            );
            $dbh->exec(" DELETE FROM CURRENT_PLAYLIST_TRACK WHERE playlist_id = :playlist_id ", $params);
            // TODO: gen indexes
            if (is_array($this->tracks) && count($this->tracks) > 0) {
                foreach ($this->tracks as $trackIndex => $trackId) {
                    /*
                    $params = array(
                        new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                        new \aportela\DatabaseWrapper\Param\StringParam(":track_id", $trackId),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":track_index", $trackIndex)
                    );
                    $dbh->exec(" INSERT INTO PLAYLIST_TRACK (playlist_id, track_id, track_index) VALUES(:playlist_id, :track_id, :track_index) ", $params);
                    */
                }
            }
        } else {
            throw new \Spieldose\Exception\UnauthorizedException("");
        }
    }

    public function empty(\aportela\DatabaseWrapper\DB $dbh)
    {
        $this->currentIndex = -1;
        $this->radioStation->id = null;
        $this->tracks = [];
        $this->save($dbh);
    }
}
