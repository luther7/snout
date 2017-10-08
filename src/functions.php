<?php
namespace Snout;

use InvalidArgumentException;
use Ds\Map;
use Ds\Set;
use Snout\Exceptions\ConfigurationException;

/**
 * Recursivly convert an array into a Map.
 *
 * @param   array $argument
 * @returns Map
 */
function array_to_map(array $argument) : Map
{
    $argument = new Map($argument);
    $argument->apply(
        function ($key, $value) {
            return is_array($value) ? array_to_map($value) : $value;
        }
    );

    return $argument;
}

/**
 * Return file at path JSON decoded and converted into a Map.
 *
 * @param string $path
 * @param bool   $assert Throw an exception if the file is not found or the
 *                       contents are invalid JSON.
 * @return ?Map          Decoded file as a Map.
 * @throws Exception On invalid json and asserting or on file not found.
 */
function json_decode_file(string $path, bool $assert = true) : ?Map
{
    if (!file_exists($path) || !($contents = file_get_contents($path))) {
        if ($assert) {
            throw new \Exception("File not found: {$path}");
        }

        return null;
    }

    $contents = json_decode($contents, true);

    if ($contents === null) {
        if ($assert) {
            throw new \Exception('Could not decode JSON.');
        }

        return null;
    }

    return array_to_map($contents);
}

/**
 * Form a config.
 *
 * @param  array|Map $config
 * @param  array     $default
 * @return Map
 * @throws InvalidArgumentException If config is not an array or Map.
 */
function form_config($config, array $default = null) : Map
{
    if (is_array($config)) {
        $config = array_to_map($config);
    } elseif (!($config instanceof Map)) {
        throw new InvalidArgumentException(
            '$config must be an array or an instance of \Ds\Map.'
        );
    }

    if ($default === null) {
        return $config;
    }

    $default_config = array_to_map($default);

    return $default_config->merge($config);
}

/**
 * Check a config for required keys.
 *
 * @param  Set  $required
 * @param  Map  $config
 * @return void
 * @throws ConfigurationException
 */
function check_config(Set $required, Map $config) : void
{
    $missing = $required->diff($config->keys());

    if ($missing->isEmpty()) {
        return;
    }

    throw new ConfigurationException(
        "Invalid configuration. Missing keys: '" . $missing->join("', '") . "'."
    );
}
