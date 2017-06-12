<?php
namespace Snout\Exceptions;

/**
 * Config Exception.
 */
class ConfigException extends \Exception
{
    /**
     * @param string    $message
     * @param string    $path     Path to config.
     * @param int       $code
     * @param Exception $previous
     */
    public function __construct($message = null, string $path, $code = 0, Exception $previous = null)
    {
        $message .= " Using config at {$path}.";

        parent::__construct($message, $code, $previous);
    }
}