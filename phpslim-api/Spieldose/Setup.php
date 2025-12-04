<?php

declare(strict_types=1);

namespace Spieldose;

class Setup
{
    private readonly \Spieldose\Settings $settings;

    public function __construct(private readonly \Psr\Log\LoggerInterface $logger)
    {
        // this is also required for some constructor operations (error_reporting/ini_set/date_default_timezone_set)
        $this->settings = new \Spieldose\Settings();
    }

    /**
     * @return array<string>
     */
    public function getMissingPHPExtensions(): array
    {
        return (array_diff(\Spieldose\Settings::PHP_REQUIRED_EXTENSIONS, get_loaded_extensions()));
    }

    public function checkRequiredPHPExtensions(): bool
    {
        $missingExtensions = $this->getMissingPHPExtensions();
        if ($missingExtensions !== []) {
            $this->logger->critical("Error: missing php extension/s: ", $missingExtensions);
            return (false);
        } else {
            return (true);
        }
    }

    public function createMissingPaths(): bool
    {
        // TODO: new settings
        /*
        if (!file_exists($this->settings['thumbnails']['artists']['basePath'])) {
            if (!mkdir($this->settings['thumbnails']['artists']['basePath'], 0750, true)) {
                $this->logger->critical("Error creating artist thumbnail basePath: " . $this->settings['thumbnails']['artists']['basePath']);
                return (false);
            }
        }
        if (!file_exists($this->settings['thumbnails']['albums']['basePath'])) {
            if (!mkdir($this->settings['thumbnails']['albums']['basePath'], 0750, true)) {
                $this->logger->critical("Error creating album thumbnail basePath: " . $this->settings['thumbnails']['albums']['basePath']);
                return (false);
            }
        }
        if (!file_exists($this->settings['thumbnails']['radioStations']['basePath'])) {
            if (!mkdir($this->settings['thumbnails']['radioStations']['basePath'], 0750, true)) {
                $this->logger->critical("Error creating radio station thumbnail basePath: " . $this->settings['thumbnails']['radioStations']['basePath']);
                return (false);
            }
        }
        */

        $path = $this->settings->getCachePath("MusicBrainz");
        if (!file_exists($path) && !mkdir($path, 0o750, true)) {
            $this->logger->critical("Error creating MusicBrainz cache basePath: " . $path);
            return (false);
        }

        $path = $this->settings->getCachePath("LastFM");
        if (!file_exists($path) && !mkdir($path, 0o750, true)) {
            $this->logger->critical("Error creating LastFM cache basePath: " . $path);
            return (false);
        }

        $path = $this->settings->getCachePath("Wikipedia");
        if (!file_exists($path) && !mkdir($path, 0o750, true)) {
            $this->logger->critical("Error creating Wikipedia cache basePath: " . $path);
            return (false);
        }

        $path = $this->settings->getCachePath("Lyrics");
        if (!file_exists($path) && !mkdir($path, 0o750, true)) {
            $this->logger->critical("Error creating Lyrics cache basePath: " . $path);
            return (false);
        }

        return (true);
    }
}
