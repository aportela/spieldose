<?php

declare(strict_types=1);

namespace HomeDocs;

class CmdLine
{
    private $options = array();

    /**
     * commandline constructor
     *
     * @param $short string Each character in this string will be used as option characters and matched against options passed to the script starting with a single hyphen (-). For example, an option string "x" recognizes an option -x. Only a-z, A-Z and 0-9 are allowed.
     * @param $long array An array of options. Each element in this array will be used as option strings and matched against options passed to the script starting with two hyphens (--). For example, an longopts element "opt" recognizes an option --opt.
     *
     */
    public function __construct(string $short, array $long)
    {
        $this->options = getopt($short, $long);
    }

    public function __destruct()
    {
    }

    /**
     * Check for commandline parameter existence
     *
     * @param string $param the parameter name to check
     *
     */
    public function hasParam(string $param): bool
    {
        return (array_key_exists($param, $this->options));
    }

    /**
     * Get commandline parameter value
     *
     * @param string $key the parameter name to obtain the key
     *
     */
    public function getParamValue(string $key): string
    {
        return (isset($this->options[$key]) ? $this->options[$key] : null);
    }
}
