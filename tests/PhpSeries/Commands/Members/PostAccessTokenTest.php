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
        $this->assertCommandParameterHasBadType('client_id', 666);
        $this->assertCommandParameterHasBadType('client_secret', 666);
        $this->assertCommandParameterHasBadType('redirect_uri', 666);
        $this->assertCommandParameterHasBadType('code', 666);

        // Mandatory parameters
        $this->assertCommandParameterIsMandatory('client_id');
        $this->assertCommandParameterIsMandatory('client_secret');
        $this->assertCommandParameterIsMandatory('redirect_uri');
        $this->assertCommandParameterIsMandatory('code');

        // Not defined parameter
        $this->assertCommandParameterIsNotDefined('foobar');
    }
}