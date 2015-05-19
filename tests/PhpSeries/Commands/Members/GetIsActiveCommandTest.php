<?php
namespace Tests\PhpSeries\Commands\Members;

use Tests\PhpSeries\Commands\CommandTestCase;

/**
 * Class GetIsActiveCommandTest
 *
 * @package Tests\PhpSeries\Commands\Members
 */
class GetIsActiveCommandTest extends CommandTestCase
{
    /**
     * @return string
     */
    protected function getCommandClassName()
    {
        return '\PhpSeries\Commands\Members\GetIsActiveCommand';
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