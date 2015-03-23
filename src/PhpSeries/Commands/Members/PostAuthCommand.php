<?php
namespace PhpSeries\Commands\Members;

use PhpSeries\Commands\AbstractCommand;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PostAuthCommand
 *
 * @package PhpSeries\Commands\Members
 */
class PostAuthCommand extends AbstractCommand
{
    /**
     * @return array
     */
    protected function resolveParameters(array $parameters)
    {
        return (new OptionsResolver())
            ->setRequired(['login', 'password'])
            ->setAllowedTypes(
                [
                    'login'    => 'string',
                    'password' => 'string'
                ]
            )
            ->resolve($parameters);
    }
}