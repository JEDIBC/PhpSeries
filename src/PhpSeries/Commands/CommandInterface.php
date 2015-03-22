<?php
namespace PhpSeries\Commands;

/**
 * Interface CommandInterface
 */
interface CommandInterface
{
    /**
     * @param string $method
     * @param string $url
     * @param array  $parameters
     *
     * @return mixed
     */
    public function execute($method, $url, array $parameters);
}