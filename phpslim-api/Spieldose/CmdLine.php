<?php

declare(strict_types=1);

namespace Spieldose;

class CmdLine
{
    /**
     * @var array<string, list<mixed>|false>
     */
    private array|false $options = [];

    /**
     * commandline constructor
     *
     * @param string $short Each character in this string will be used as option characters and matched against options passed to the script starting with a single hyphen (-). For example, an option string "x" recognizes an option -x. Only a-z, A-Z and 0-9 are allowed.
     * @param array<string> $long An array of options. Each element in this array will be used as option strings and matched against options passed to the script starting with two hyphens (--). For example, an longopts element "opt" recognizes an option --opt.
     *
     */
    public function __construct(string $short, array $long)
    {
        $this->options = getopt($short, $long);
        if (! is_array($this->options)) {
            throw new \RuntimeException("Failed to get commandline options");
        }
    }

    public function hasOptions(): bool
    {
        return (count($this->options) > 0);
    }

    /**
     * Check for commandline parameter existence
     *
     * @param string $param the parameter name to check
     *
     */
    public function hasParam(string $param): bool
    {
        return is_array($this->options) && array_key_exists($param, $this->options);
    }

    /**
     * Get commandline parameter value
     */
    public function getParamValue(string $param): ?string
    {
        if (!is_array($this->options)) {
            return null;
        }

        if (!array_key_exists($param, $this->options)) {
            return null;
        }

        if (! is_array($this->options[$param])) {
            return strval($this->options[$param]);
        } else {
            return (null);
        }
    }
}
