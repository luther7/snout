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

/**
 * @param  string   $type
 * @return callable Casting function.
 * @throws ConfigException On invalid cast type.
 */
function get_casting_function(string $type) : callable
{
    $map = new Map([
        'string'  => 'strval',
        'integer' => 'intval',
        'boolean' => 'boolval',
        'float'   => 'doubleval'
    ]);

    $nullable = false;
    $caster = null;

    // Check for '?' prefix which indicates optional 'null'.
    if (mb_substr($type, 0, 1) == '?') {
        $nullable = true;
        $type = mb_substr($type, 1);
    }

    if (!$map->hasKey($type)) {
        throw new ConfigurationException("Unknown casting type '{$type}'.");
    }

    if (!$nullable) {
        return $map->get($type);
    }

    $caster = $map->get($type);

    return function ($value) use ($caster) {
        if (empty($value)) {
            return null;
        }

        return $caster($value);
    };
}
