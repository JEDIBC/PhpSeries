<?php
namespace Tests\PhpSeries\Commands\Members;

use Tests\PhpSeries\Commands\CommandTestCase;

/**
 * Class PostDestroyCommandTest
 *
 * @package Tests\PhpSeries\Commands\Members
 */
class PostDestroyCommandTest extends CommandTestCase
{
    /**
     * @return string
     */
    protected function getCommandClassName()
    {
        return '\PhpSeries\Commands\Members\PostDestroyCommand';
    }

    /**
     * @return array
     */
    protected function getParameters()
    {
        return [
            'token' => 'azerty'
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
        $this->assertCommandParameterError('token', 666, 'This value should be of type string.');

        // Mandatory/optional parameters
        $this->assertCommandParameterIsMandatory('token');

        // Not defined parameter
        $this->assertCommandParameterIsNotDefined('foobar');
    }
}