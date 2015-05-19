<?php
namespace Tests\PhpSeries\Commands\Members;

use Tests\PhpSeries\Commands\CommandTestCase;

/**
 * Class GetNotificationsCommandTest
 *
 * @package Tests\PhpSeries\Commands\Members
 */
class GetNotificationsCommandTest extends CommandTestCase
{
    /**
     * @return string
     */
    protected function getCommandClassName()
    {
        return '\PhpSeries\Commands\Members\GetNotificationsCommand';
    }

    /**
     * @return array
     */
    protected function getParameters()
    {
        return [
            'token'       => 'azerty',
            'since_id'    => 666,
            'number'      => 10,
            'sort'        => 'ASC',
            'types'       => 'foo,bar',
            'auto_delete' => false
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
        $this->assertCommandParameterError('since_id', 'oimhmoh', 'This value should be of type integer.');
        $this->assertCommandParameterError('number', 'oimhmoh', 'This value should be of type integer.');
        $this->assertCommandParameterError('sort', 'gru', 'The value you selected is not a valid choice.');
        $this->assertCommandParameterError('types', 666, 'This value should be of type string.');
        $this->assertCommandParameterError('auto_delete', 666, 'This value should be of type boolean.');

        // Bad range
        $this->assertCommandParameterError('number', 0, 'This value should be 1 or more.');
        $this->assertCommandParameterError('number', 101, 'This value should be 100 or less.');

        // Mandatory/optional parameters
        $this->assertCommandParameterIsMandatory('token');
        $this->assertCommandParameterIsOptional('number');
        $this->assertCommandParameterIsOptional('since_id');
        $this->assertCommandParameterIsOptional('sort');
        $this->assertCommandParameterIsOptional('types');
        $this->assertCommandParameterIsOptional('auto_delete');

        // Not defined parameter
        $this->assertCommandParameterIsNotDefined('foobar');
    }
}