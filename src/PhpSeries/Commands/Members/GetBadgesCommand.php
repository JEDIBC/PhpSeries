<?php
namespace PhpSeries\Commands\Members;

use PhpSeries\Commands\AbstractCommand;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class GetBadgesCommand
 *
 * @package PhpSeries\Commands\Members
 */
class GetBadgesCommand extends AbstractCommand
{
    /**
     * @return array
     */
    protected function resolveParameters(array $parameters)
    {
        return (new OptionsResolver())
            ->setRequired(['token', 'id'])
            ->setAllowedTypes(
                [
                    'token' => 'string',
                    'id'    => 'integer'
                ]
            )
            ->resolve($parameters);
    }
}