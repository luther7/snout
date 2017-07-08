<?php
namespace Snout;

/**
 * Return file at path JSON decoded.
 *
 * @param string $path
 * @param bool   $assoc
 * @param bool   $assert If true, throw an exception if the file is not found or
 *                       the contents are invalid JSON.
 *
 * @return $mixed Decoded file as an object or array.
 *                null on failure and not assert.
 */
function json_decode_file(string $path, bool $assoc = false, bool $assert = true)
{
    try {
        $contents = json_decode(file_get_contents($path), $assoc);

        if ($contents === null) {
            throw new \Exception('Could not decode JSON.');
        }

        return $contents;
    } catch (\Exception $e) {
        if ($assert) {
            throw $e;
        }

        return null;
    }
}
