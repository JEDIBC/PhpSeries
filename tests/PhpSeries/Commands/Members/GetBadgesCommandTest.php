<?php
namespace Tests\PhpSeries\Commands\Members;

use Tests\PhpSeries\Commands\CommandTestCase;

/**
 * Class GetBadgesCommandTest
 *
 * @package Tests\PhpSeries\Commands\Members
 */
class GetBadgesCommandTest extends CommandTestCase
{
    /**
     * @return string
     */
    protected function getCommandClassName()
    {
        return '\PhpSeries\Commands\Members\GetBadgesCommand';
    }

    /**
     * @return array
     */
    protected function getParameters()
    {
        return [
            'token' => 'azerty',
            'id'    => 666
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
        $this->assertCommandParameterHasBadType('token', 666, 'This value should be of type string.');
        $this->assertCommandParameterHasBadType('id', 'oimhmoh', 'This value should be of type integer.');

        // Mandatory parameters
        $this->assertCommandParameterIsMandatory('token');
        $this->assertCommandParameterIsMandatory('id');

        // Not defined parameter
        $this->assertCommandParameterIsNotDefined('foobar');
    }
}