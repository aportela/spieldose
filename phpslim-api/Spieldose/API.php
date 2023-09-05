<?php

declare(strict_types=1);

namespace Spieldose;

class API
{

    public const FILE_URL = "/api/2/file/%s";

    public const LOCAL_COVER_PATH_SMALL_THUMBNAIL = "/api/2/thumbnail/small/local/album/?path=%s";
    public const LOCAL_COVER_PATH_NORMAL_THUMBNAIL = "/api/2/thumbnail/normal/local/album/?path=%s";

    public const REMOTE_COVER_URL_SMALL_THUMBNAIL = "/api/2/thumbnail/small/remote/album/?url=%s";
    public const REMOTE_COVER_URL_NORMAL_THUMBNAIL = "/api/2/thumbnail/normal/remote/album/?url=%s";

    public const CACHED_HASH_SMALL_THUMBNAIL = "/api/2/cache/thumbnail/small/%s";
    public const CACHED_HASH_NORMAL_THUMBNAIL = "/api/2/cache/thumbnail/normal/%s";
}
