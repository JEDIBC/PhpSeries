<?php
namespace PhpSeries\Commands\Members;

use PhpSeries\Commands\AbstractCommand;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PostAccessTokenCommand
 *
 * @package PhpSeries\Commands\Members
 */
class PostAccessTokenCommand extends AbstractCommand
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureParameters(OptionsResolver $resolver)
    {
        $resolver->setRequired(['client_id', 'client_secret', 'redirect_uri', 'code'])
            ->setAllowedTypes(
                [
                    'client_id'     => 'string',
                    'client_secret' => 'string',
                    'redirect_uri'  => 'string',
                    'code'          => 'string'
                ]
            );
    }
}