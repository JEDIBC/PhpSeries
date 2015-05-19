<?php
namespace PhpSeries\Commands\Members;

use PhpSeries\Commands\AbstractCommand;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class GetNotificationsCommand
 *
 * @package PhpSeries\Commands\Members
 */
class GetNotificationsCommand extends AbstractCommand
{
    /**
     * @return Assert\Collection
     */
    protected function getConstraint()
    {
        return new Assert\Collection(
            [
                'fields' => [
                    'token'       => [
                        new Assert\NotBlank(),
                        new Assert\Type('string')
                    ],
                    'since_id'    => [
                        new Assert\Optional(
                            [
                                new Assert\NotBlank(),
                                new Assert\Type('integer')
                            ]
                        )
                    ],
                    'number'      => [
                        new Assert\Optional(
                            [
                                new Assert\NotBlank(),
                                new Assert\Type('integer'),
                                new Assert\Range(
                                    [
                                        'min' => 1,
                                        'max' => 100
                                    ]
                                )
                            ]
                        )
                    ],
                    'sort'        => [
                        new Assert\Optional(
                            [
                                new Assert\NotBlank(),
                                new Assert\Choice(['ASC', 'DESC'])
                            ]
                        )
                    ],
                    'types'       => [
                        new Assert\Optional(
                            [
                                new Assert\NotBlank(),
                                new Assert\Type('string')
                            ]
                        )
                    ],
                    'auto_delete' => [
                        new Assert\Optional(
                            [
                                new Assert\Type('boolean')
                            ]
                        )
                    ],
                ]
            ]
        );
    }
}