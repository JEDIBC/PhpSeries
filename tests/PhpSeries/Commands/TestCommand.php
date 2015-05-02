<?php

namespace Tests\PhpSeries\Commands;

use PhpSeries\Commands\AbstractCommand;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TestCommand
 *
 * @package Tests\PhpSeries\Commands
 */
class TestCommand extends AbstractCommand
{

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureParameters(OptionsResolver $resolver)
    {
        $resolver->setRequired(['foo', 'gru'])->setAllowedTypes(
            [
                'foo' => 'string',
                'gru' => 'string'
            ]
        );
    }

}