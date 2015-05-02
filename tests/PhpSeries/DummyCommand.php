<?php
namespace Tests\PhpSeries;

use PhpSeries\Commands\AbstractCommand;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DummyCommand
 *
 * @package Tests\PhpSeries
 */
class DummyCommand extends AbstractCommand
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureParameters(OptionsResolver $resolver)
    {
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