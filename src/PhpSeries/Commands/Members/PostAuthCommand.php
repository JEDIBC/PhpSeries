<?php
namespace PhpSeries\Commands\Members;

use PhpSeries\Commands\AbstractCommand;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PostAuthCommand
 *
 * @package PhpSeries\Commands\Members
 */
class PostAuthCommand extends AbstractCommand
{
    /**
     * @return Assert\Collection
     */
    protected function getConstraint()
    {
        return new Assert\Collection(
            [
                'fields' => [
                    'login'    => [
                        new Assert\NotBlank(),
                        new Assert\Type('string')
                    ],
                    'password' => [
                        new Assert\NotBlank(),
                        new Assert\Type('string')
                    ]
                ]
            ]
        );
    }
}