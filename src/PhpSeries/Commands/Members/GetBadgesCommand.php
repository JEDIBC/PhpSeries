<?php
namespace PhpSeries\Commands\Members;

use PhpSeries\Commands\AbstractCommand;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class GetBadgesCommand
 *
 * @package PhpSeries\Commands\Members
 */
class GetBadgesCommand extends AbstractCommand
{
    /**
     * @return Assert\Collection
     */
    protected function getConstraint()
    {
        return new Assert\Collection(
            [
                'fields' => [
                    'token' => [
                        new Assert\NotBlank(),
                        new Assert\Type('string')
                    ],
                    'id'    => [
                        new Assert\NotBlank(),
                        new Assert\Type('integer')
                    ]
                ]
            ]
        );
    }
}