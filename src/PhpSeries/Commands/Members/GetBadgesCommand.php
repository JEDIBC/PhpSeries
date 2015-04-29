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
     * @param OptionsResolver $resolver
     */
    protected function configureParameters(OptionsResolver $resolver)
    {
        $resolver->setRequired(['token', 'id'])
            ->setAllowedTypes(
                [
                    'token' => 'string',
                    'id'    => 'integer'
                ]
            );
    }
}