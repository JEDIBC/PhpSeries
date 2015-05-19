<?php
namespace Tests\PhpSeries\Commands\Members;

use Tests\PhpSeries\Commands\CommandTestCase;

/**
 * Class PostAccessTokenCommandTest
 *
 * @package Tests\PhpSeries\Commands\Members
 */
class PostAccessTokenCommandTest extends CommandTestCase
{
    /**
     * @return string
     */
    protected function getCommandClassName()
    {
        return '\PhpSeries\Commands\Members\PostAccessTokenCommand';
    }

    /**
     * @return array
     */
    protected function getParameters()
    {
        return [
            'client_id'     => 'azerty',
            'client_secret' => 'querty',
            'redirect_uri'  => 'http://foo.bar/gru',
            'code'          => 'xxxxxx'
        ];
    }

    /**
     * @test
     */
    public function testParameters()
    {
        // valid
        $this->assertCommandParametersAreValid($this->getParameters());

        // Bad types
        $this->assertCommandParameterError('client_id', 666, 'This value should be of type string.');
        $this->assertCommandParameterError('client_secret', 666, 'This value should be of type string.');
        $this->assertCommandParameterError('redirect_uri', 666, 'This value should be of type string.');
        $this->assertCommandParameterError('redirect_uri', 'gru', 'This value is not a valid URL.');
        $this->assertCommandParameterError('code', 666, 'This value should be of type string.');

        // Mandatory parameters
        $this->assertCommandParameterIsMandatory('client_id');
        $this->assertCommandParameterIsMandatory('client_secret');
        $this->assertCommandParameterIsMandatory('redirect_uri');
        $this->assertCommandParameterIsMandatory('code');

        // Not defined parameter
        $this->assertCommandParameterIsNotDefined('foobar');
    }
}