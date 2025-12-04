<?php

declare(strict_types=1);

namespace Spieldose;

class API
{
    public const BASE_PATH = "/api2";

    public const FILE_URL = self::BASE_PATH . "/file/%s";

    public const REMOTE_ARTIST_URL_SMALL_THUMBNAIL = self::BASE_PATH . "/thumbnail/small/remote/artist/?url=%s";

    public const REMOTE_ARTIST_URL_NORMAL_THUMBNAIL = self::BASE_PATH . "/thumbnail/normal/remote/artist/?url=%s";

    public const LOCAL_COVER_PATH_SMALL_THUMBNAIL = self::BASE_PATH . "/thumbnail/small/local/album/?path=%s";

    public const LOCAL_COVER_PATH_NORMAL_THUMBNAIL = self::BASE_PATH . "/thumbnail/normal/local/album/?path=%s";

    public const REMOTE_COVER_URL_SMALL_THUMBNAIL = self::BASE_PATH . "/thumbnail/small/remote/album/?url=%s";

    public const REMOTE_COVER_URL_NORMAL_THUMBNAIL = self::BASE_PATH . "/thumbnail/normal/remote/album/?url=%s";

    public const REMOTE_RADIOSTATION_URL_SMALL_THUMBNAIL = self::BASE_PATH . "/thumbnail/small/remote/radiostation/?url=%s";

    public const REMOTE_RADIOSTATION_URL_NORMAL_THUMBNAIL = self::BASE_PATH . "/thumbnail/normal/remote/radiostation/?url=%s";

    public const CACHED_HASH_SMALL_THUMBNAIL = self::BASE_PATH . "/cache/thumbnail/small/%s";

    public const CACHED_HASH_NORMAL_THUMBNAIL = self::BASE_PATH . "/cache/thumbnail/normal/%s";
}
