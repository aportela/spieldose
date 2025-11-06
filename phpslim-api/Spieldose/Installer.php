<?php

namespace Spieldose;

class Installer
{
    public const ALGORITHM = 'HS256';

    private \Psr\Log\LoggerInterface $logger;
    private array $settings = [];

    public function __construct(\Psr\Log\LoggerInterface $logger, \Psr\Container\ContainerInterface $container)
    {
        $this->logger = $logger;
        $this->settings = $container->get("settings");
    }

    public function __destruct() {}

    public function getMissingPHPExtensions(): array
    {
        return (array_diff($this->settings["phpRequiredExtensions"], get_loaded_extensions()));
    }

    public function checkRequiredPHPExtensions(): bool
    {
        $missingExtensions = $this->getMissingPHPExtensions();
        if (count($missingExtensions) > 0) {
            $this->logger->critical("Error: missing php extension/s: ", $missingExtensions);
            return (false);
        } else {
            return (true);
        }
    }

    public function createRequiredMissingPaths(): bool
    {
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
        if (!file_exists($this->settings["cache"]["MusicBrainzCachePath"])) {
            if (!mkdir($this->settings["cache"]["MusicBrainzCachePath"], 0750, true)) {
                $this->logger->critical("Error creating MusicBrainz cache basePath: " . $this->settings["cache"]["MusicBrainzCachePath"]);
                return (false);
            }
        }
        if (!file_exists($this->settings["cache"]["LastFMCachePath"])) {
            if (!mkdir($this->settings["cache"]["LastFMCachePath"], 0750, true)) {
                $this->logger->critical("Error creating LastFM cache basePath: " . $this->settings["cache"]["LastFMCachePath"]);
                return (false);
            }
        }
        if (!file_exists($this->settings["cache"]["LyricsCachePath"])) {
            if (!mkdir($this->settings["cache"]["LyricsCachePath"], 0750, true)) {
                $this->logger->critical("Error creating LastFM cache basePath: " . $this->settings["cache"]["LyricsCachePath"]);
                return (false);
            }
        }
        if (!file_exists($this->settings["cache"]["WikipediaCachePath"])) {
            if (!mkdir($this->settings["cache"]["WikipediaCachePath"], 0750, true)) {
                $this->logger->critical("Error creating Wikipedia cache basePath: " . $this->settings["cache"]["WikipediaCachePath"]);
                return (false);
            }
        }
        return (true);
    }
}
