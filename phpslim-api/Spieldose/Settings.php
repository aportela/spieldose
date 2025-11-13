<?php

declare(strict_types=1);

namespace Spieldose;

class Settings
{
    public const array PHP_REQUIRED_EXTENSIONS = ['pdo_sqlite', 'mbstring', 'curl', 'dom', 'libxml', 'SimpleXML'];

    public const int DEFAULT_RESULTS_PAGE = 32;

    /**
     * @var array<string,mixed>
     */
    private array $settings = [];

    public function __construct()
    {
        $settingsPath =  dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'settings.php';
        if (file_exists($settingsPath)) {
            /** @phpstan-ignore-next-line */
            $this->settings = require $settingsPath;
            if ($this->getEnvironment() === 'development') {
                error_reporting(E_ALL);
                ini_set('display_errors', '1');
            } else {
                error_reporting(0);
                ini_set('display_errors', '0');
            }

            date_default_timezone_set($this->getDefaultTimezone());
        } else {
            throw new \RuntimeException("Settings file not found: " . $settingsPath);
        }
    }

    public function getEnvironment(): string
    {
        if (is_string($this->settings['environment'])) {
            return ($this->settings['environment']);
        } else {
            throw new \RuntimeException("Settings key (environment) not found");
        }
    }

    protected function getDefaultTimezone(): string
    {
        if (is_string($this->settings['defaultTimezone'])) {
            return ($this->settings['defaultTimezone']);
        } else {
            throw new \RuntimeException("Settings key (defaultTimezone) not found");
        }
    }

    public function allowSignUp(): bool
    {
        if (is_array($this->settings['common']) && is_bool($this->settings['common']['allowSignUp'])) {
            return ($this->settings['common']['allowSignUp']);
        } else {
            throw new \RuntimeException("Settings key (common->allowSignUp) not found");
        }
    }

    public function getJWTPassphrase(): string
    {
        if (is_array($this->settings['jwt']) && is_string($this->settings['jwt']['passphrase'])) {
            return ($this->settings['jwt']['passphrase']);
        } else {
            throw new \RuntimeException("Settings key (jwt->passphrase) not found");
        }
    }

    public function getErrorBooleanKey(string $key): bool
    {
        if (is_array($this->settings['error']) && is_bool($this->settings['error'][$key])) {
            return ($this->settings['error'][$key]);
        } else {
            throw new \RuntimeException(sprintf('Settings key (error->%s) not found', $key));
        }
    }

    public function getLoggerDefaultLevel(): \Monolog\Level
    {
        if (is_array($this->settings['logger']) && $this->settings['logger']['defaultLevel'] instanceof \Monolog\Level) {
            return ($this->settings['logger']['defaultLevel']);
        } else {
            throw new \RuntimeException("Settings key (logger->defaultLevel) not found");
        }
    }

    public function getLoggerChannelProperty(string $channel, string $property): string
    {
        if (is_array($this->settings['logger']) && is_array($this->settings['logger']['channels']) && is_array($this->settings['logger']['channels'][$channel]) && is_string($this->settings['logger']['channels'][$channel][$property])) {
            return ($this->settings['logger']['channels'][$channel][$property]);
        } else {
            throw new \RuntimeException(sprintf('Settings key (logger->channels->%s->%s) not found', $channel, $property));
        }
    }

    public function getDatabasePath(): string
    {
        if (is_array($this->settings['db']) && is_string($this->settings['db']['database'])) {
            return ($this->settings['db']['database']);
        } else {
            throw new \RuntimeException("Settings key (db->database) not found");
        }
    }

    /**
     * @return array<int, bool|int>
     */
    public function getDatabasePDOOptions(): array
    {
        if (is_array($this->settings['db']) && is_array($this->settings['db']['options'])) {
            /**
             * @var array<int, bool|int>
             */
            $options = $this->settings['db']['options'];
            return ($options);
        } else {
            throw new \RuntimeException("Settings key (db->options) not found");
        }
    }

    public function getDatabaseUpgradeSchemaPath(): string
    {
        if (is_array($this->settings['db']) && is_string($this->settings['db']['upgradeSchemaPath'])) {
            return ($this->settings['db']['upgradeSchemaPath']);
        } else {
            throw new \RuntimeException("Settings key (db->upgradeSchemaPath) not found");
        }
    }
}
