<?php
namespace Tests\PhpSeries\Commands\Members;

use Tests\PhpSeries\Commands\CommandTestCase;

/**
 * Class PostAuthCommandTest
 *
 * @package Tests\PhpSeries\Commands\Members
 */
class PostAuthCommandTest extends CommandTestCase
{
    /**
     * @return string
     */
    protected function getCommandClassName()
    {
        return '\PhpSeries\Commands\Members\PostAuthCommand';
    }

    /**
     * @return array
     */
    protected function getParameters()
    {
        return [
            'login'    => 'foo',
            'password' => 'bar'
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
        $this->assertCommandParameterError('login', 666, 'This value should be of type string.');
        $this->assertCommandParameterError('password', 666, 'This value should be of type string.');

        // Mandatory parameters
        $this->assertCommandParameterIsMandatory('login');
        $this->assertCommandParameterIsMandatory('password');

        // Not defined parameter
        $this->assertCommandParameterIsNotDefined('foobar');
    }
}