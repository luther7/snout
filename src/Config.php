<?php
namespace Snout;

use \Snout\Exceptions\ConfigException;

/**
 * Config.
 */
class Config
{
    /**
     * @var array $config
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param string|array $option One or more options.
     * @param bool         $assert Throw an exception if a value for an option
     *                             is not found.
     *
     * @return mixed
     */
    public function get($option, bool $assert = false)
    {
        if (is_string($option)) {
            return $this->getSingle($option, $assert);
        } elseif (is_array($option)) {
            $result = [];

            foreach ($option as $single_option) {
                $result[$single_option] = $this->getSingle(
                    $single_option,
                    $assert
                );
            }

            return $result;
        } else {
            throw new \InvalidArgumentException(
                "Argument 'option' must be a string or an array of strings."
            );
        }
    }

    /**
     * @param string|array $option An option or an array of option value pairs.
     * @param mixed        $value  A value or null
     *
     * @return void
     */
    public function set($option, $value = null) : void
    {
        if (is_string($option)) {
            if ($value === null) {
                throw new \InvalidArgumentException(
                    "Second argument 'value' required."
                );
            }

            $option = [$option => $value];
        } elseif (!is_array($option)) {
            throw new \InvalidArgumentException(
                "Argument 'option' must be a string or an array of option value "
                . "pairs."
            );
        }

        foreach ($option as $single_option => $value) {
            $this->config[$single_option] = $value;
        }
    }

    /**
     * @param string|array $key    One or more keys
     * @param bool         $assert
     *
     * @return mixed
     */
    private function getSingle($key, bool $assert = false)
    {
        $value = $this->config[$key] ?? null;

        if ($value === null && $assert) {
            throw new ConfigException(
                "Required config value is missing: {$key}."
            );
        }

        return $value;
    }
}
