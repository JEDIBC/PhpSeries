<?php
namespace Tests\PhpSeries\Commands\Members;

use Tests\PhpSeries\Commands\CommandTestCase;

/**
 * Class GetInfosCommandTest
 *
 * @package Tests\PhpSeries\Commands\Members
 */
class GetInfosCommandTest extends CommandTestCase
{
    /**
     * @return string
     */
    protected function getCommandClassName()
    {
        return '\PhpSeries\Commands\Members\GetInfosCommand';
    }

    /**
     * @return array
     */
    protected function getParameters()
    {
        return [
            'token'   => 'azerty',
            'id'      => 666,
            'summary' => true,
            'only'    => 'shows'
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
        $this->assertCommandParameterError('id', 'oimhmoh', 'This value should be of type integer.');
        $this->assertCommandParameterError('summary', 'oimhmoh', 'This value should be of type boolean.');
        $this->assertCommandParameterError('only', 'gru', 'The value you selected is not a valid choice.');

        // Mandatory/optional parameters
        $this->assertCommandParameterIsOptional('token');
        $this->assertCommandParameterIsMandatory('id');
        $this->assertCommandParameterIsOptional('summary');
        $this->assertCommandParameterIsMandatory('only');

        // Not defined parameter
        $this->assertCommandParameterIsNotDefined('foobar');
    }
}