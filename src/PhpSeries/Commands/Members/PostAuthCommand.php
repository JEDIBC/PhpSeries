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
     * @param OptionsResolver $resolver
     */
    protected function configureParameters(OptionsResolver $resolver)
    {
        $resolver->setRequired(['login', 'password'])
            ->setAllowedTypes(
                [
                    'login'    => 'string',
                    'password' => 'string'
                ]
            );
    }
}