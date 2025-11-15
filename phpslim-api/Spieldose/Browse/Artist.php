<?php

declare(strict_types=1);

namespace Spieldose\Browse;

class Artist extends \Spieldose\Browse\Base
{
    public string $name;
    public string $mbId;
    public string $image;
    public int $totalAlbums = 0;
    public int $totalTracks = 0;

    /**
     * @return array<\Spieldose\Browse\Artist>
     */
    public function browse(): array
    {
        return (
            $this->dbh->query(
                "
                    SELECT coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.artist) AS name, FILE_ID3_TAG.mb_artist_id AS mbId, CACHE_LASTFM_ARTIST.image, 0 AS totalAlbums, 0 AS totalTracks
                    FROM FILE_ID3_TAG
                    LEFT JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = FILE_ID3_TAG.mb_artist_id
                    LEFT JOIN CACHE_LASTFM_ARTIST ON CACHE_LASTFM_ARTIST.name = coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.artist)
                    WHERE FILE_ID3_TAG.artist IS NOT NULL

                    UNION

                    SELECT coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.album_artist) AS artistName, FILE_ID3_TAG.mb_album_artist_id AS mbId, CACHE_LASTFM_ARTIST.image, 0 AS totalAlbums, 0 AS totalTracks
                    FROM FILE_ID3_TAG
                    LEFT JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = FILE_ID3_TAG.mb_album_artist_id
                    LEFT JOIN CACHE_LASTFM_ARTIST ON CACHE_LASTFM_ARTIST.name = coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.album_artist)
                    WHERE FILE_ID3_TAG.album_artist IS NOT NULL

                    ORDER BY 1
                "
            )
        );
    }
}
