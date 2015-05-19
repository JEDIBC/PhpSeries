<?php
namespace PhpSeries\Commands\Members;

use PhpSeries\Commands\AbstractCommand;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PostLostCommand
 *
 * @package PhpSeries\Commands\Members
 */
class PostLostCommand extends AbstractCommand
{
    /**
     * @return Assert\Collection
     */
    protected function getConstraint()
    {
        return new Assert\Collection(
            [
                'fields' => [
                    'find' => [
                        new Assert\NotBlank(),
                        new Assert\Type('string')
                    ]
                ]
            ]
        );
    }
}