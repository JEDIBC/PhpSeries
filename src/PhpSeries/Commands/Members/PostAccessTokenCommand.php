<?php
namespace PhpSeries\Commands\Members;

use PhpSeries\Commands\AbstractCommand;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PostAccessTokenCommand
 *
 * @package PhpSeries\Commands\Members
 */
class PostAccessTokenCommand extends AbstractCommand
{
    /**
     * @return Assert\Collection
     */
    protected function getConstraint()
    {
        return new Assert\Collection(
            [
                'fields' => [
                    'client_id'     => [
                        new Assert\NotBlank(),
                        new Assert\Type('string')
                    ],
                    'client_secret' => [
                        new Assert\NotBlank(),
                        new Assert\Type('string')
                    ],
                    'redirect_uri'  => [
                        new Assert\NotBlank(),
                        new Assert\Type('string'),
                        new Assert\Url()
                    ],
                    'code'          => [
                        new Assert\NotBlank(),
                        new Assert\Type('string')
                    ]
                ]
            ]
        );
    }
}