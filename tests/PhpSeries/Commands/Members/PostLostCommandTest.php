<?php
namespace Tests\PhpSeries\Commands\Members;

use Tests\PhpSeries\Commands\CommandTestCase;

/**
 * Class PostLostCommandTest
 *
 * @package Tests\PhpSeries\Commands\Members
 */
class PostLostCommandTest extends CommandTestCase
{
    /**
     * @return string
     */
    protected function getCommandClassName()
    {
        return '\PhpSeries\Commands\Members\PostLostCommand';
    }

    /**
     * @return array
     */
    protected function getParameters()
    {
        return [
            'find' => 'azerty'
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
        $this->assertCommandParameterError('find', 666, 'This value should be of type string.');

        // Mandatory parameters
        $this->assertCommandParameterIsMandatory('find');

        // Not defined parameter
        $this->assertCommandParameterIsNotDefined('foobar');
    }
}