<?php
namespace PhpSeries\Commands\Members;

use PhpSeries\Commands\AbstractCommand;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PostLostCommand
 *
 * @package PhpSeries\Commands\Members
 */
class GetInfosCommand extends AbstractCommand
{
    /**
     * @return Assert\Collection
     */
    protected function getConstraint()
    {
        return new Assert\Collection(
            [
                'fields' => [
                    'token'   => [
                        new Assert\Optional(
                            [
                                new Assert\NotBlank(),
                                new Assert\Type('string')
                            ]
                        ),
                    ],
                    'id'      => [
                        new Assert\NotBlank(),
                        new Assert\Type('integer')
                    ],
                    'summary' => [
                        new Assert\Optional(
                            [
                                new Assert\Type('boolean')
                            ]
                        )
                    ],
                    'only'    => [
                        new Assert\Choice(['movies', 'shows'])
                    ]
                ]
            ]
        );
    }
}