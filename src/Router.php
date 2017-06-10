<?php
namespace Snout;

/**
 */
class Router
{
    /** @var array Config */
    private $config;

    /**
     * @param array $config Client configuration settings.
     *
     * @see \GuzzleHttp\RequestOptions for a list of available request options.
     */
    public function __construct(array $config = [])
    {
    }

    public function route(string $path)
    {
    }
}