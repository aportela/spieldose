<?php

declare(strict_types=1);

namespace Spieldose\Library;

enum ID3TAGType
{
    case TITLE;
    case TRACK_ARTIST_NAME;
    case ALBUM_ARTIST_NAME;
    case ALBUM;
    case GENRE;
    case TRACK_NUMBER;
    case DISC_NUMBER;
    case YEAR;
    case ORIGINAL_YEAR;
    case PLAYTIME_SECONDS;
    case PLAYTIME_STRING;
    case BITRATE;
    case MIME_TYPE;
    case MB_ARTIST_ID;
    case MB_ALBUM_ARTIST_ID;
    case MB_RELEASE_GROUP_ID;
    case MB_RELEASE_ID;
    case MB_RELEASE_TRACK_ID;
}
