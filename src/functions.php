<?php
namespace Snout;

use Ds\Map;
use Ds\Set;
use Snout\Exceptions\ConfigurationException;

/**
 * Recursivly convert an array to a Map.
 *
 * @param array $argument
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
 * Return file at path JSON decoded into a Map.
 *
 * @param string $path
 * @param bool   $assert If true, throw an exception if the file is not found or
 *                       the contents are invalid JSON.
 * @throws Exception On invalid json and assert.
 * @return ?Map Decoded file as a Map or null on failure and not assert.
 */
function json_decode_file(string $path, bool $assert = true) : ?Map
{
    $contents = json_decode(file_get_contents($path), true);

    if ($contents === null) {
        if ($assert) {
            throw new \Exception('Could not decode JSON.');
        }

        return null;
    }

    return array_to_map($contents);
}

/**
 * Check a config for required keys.
 * @param Set $required
 * @param Map $config
 * @throws ConfigurationException On missing required keys.
 * @return void
 */
function check_config(Set $required, Map $config) : void
{
    $missing = $required->diff($config->keys());

    if ($missing->isEmpty()) {
        return;
    }

    throw new ConfigurationException(
        "Invalid parser configuration. Missing keys: "
        . $missing->join(', ') . '.'
    );
}