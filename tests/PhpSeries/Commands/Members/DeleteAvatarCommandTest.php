<?php
namespace Tests\PhpSeries\Commands\Members;

use Tests\PhpSeries\Commands\CommandTestCase;

/**
 * Class DeleteAvatarCommandTest
 *
 * @package Tests\PhpSeries\Commands\Members
 */
class DeleteAvatarCommandTest extends CommandTestCase
{
    /**
     * @return string
     */
    protected function getCommandClassName()
    {
        return '\PhpSeries\Commands\Members\DeleteAvatarCommand';
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