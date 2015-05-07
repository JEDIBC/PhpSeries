<?php
namespace Tests\PhpSeries;

use PhpSeries\Commands\AbstractCommand;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DummyCommand
 *
 * @package Tests\PhpSeries
 */
class DummyCommand extends AbstractCommand
{
    /**
     * @return Assert\Collection
     */
    protected function getConstraint()
    {
        return new Assert\Collection();
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $parameters
     *
     * @return array
     */
    public function execute($method, $url, array $parameters)
    {
        return ['foo' => 'bar'];
    }
}