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
        $this->assertCommandParameterHasBadType('token', 666);
        $this->assertCommandParameterHasBadType('id', 'oimhmoh');

        // Mandatory parameters
        $this->assertCommandParameterIsMandatory('token');
        $this->assertCommandParameterIsMandatory('id');

        // Not defined parameter
        $this->assertCommandParameterIsNotDefined('foobar');
    }
}