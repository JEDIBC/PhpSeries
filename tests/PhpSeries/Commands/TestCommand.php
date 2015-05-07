<?php

namespace Tests\PhpSeries\Commands;

use PhpSeries\Commands\AbstractCommand;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TestCommand
 *
 * @package Tests\PhpSeries\Commands
 */
class TestCommand extends AbstractCommand
{
    /**
     * @return Assert\Collection
     */
    protected function getConstraint()
    {
        return new Assert\Collection(
            [
                'fields' => [
                    'foo' => [
                        new Assert\NotBlank(),
                        new Assert\Type('string')
                    ],
                    'gru' => [
                        new Assert\Type('string')
                    ]
                ]
            ]
        );
    }
}