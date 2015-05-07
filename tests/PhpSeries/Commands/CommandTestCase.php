<?php
namespace Tests\PhpSeries\Commands;

use Guzzle\Http\Client;
use PhpSeries\Commands\AbstractCommand;
use Symfony\Component\Validator\Validation;

/**
 * Class CommandTestCase
 *
 * @package Tests\PhpSeries\Commands
 */
abstract class CommandTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * @return string
     */
    abstract protected function getCommandClassName();

    /**
     * @return array
     */
    abstract protected function getParameters();

    /**
     * @return AbstractCommand
     */
    protected function getCommand()
    {
        $class = $this->getCommandClassName();

        return new $class(new Client(), 'dummyKey');
    }

    /**
     * @param array $parameters
     *
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    protected function getViolations(array $parameters)
    {
        $validator = Validation::createValidator();
        $command   = $this->getCommand();

        $reflexionObject = new \ReflectionObject($command);
        $reflexionMethod = $reflexionObject->getMethod('getConstraint');
        $reflexionMethod->setAccessible(true);

        return $validator->validate($parameters, $reflexionMethod->invoke($command));
    }

    /**
     * @param string $parameter
     * @param mixed  $value
     * @param string $message
     */
    public function assertCommandParameterHasBadType($parameter, $value, $message = '')
    {
        $parameters             = $this->getParameters();
        $parameters[$parameter] = $value;

        $violations = $this->getViolations($parameters);

        $this->assertTrue($violations->count() > 0);
        $this->assertEquals(sprintf('[%s]', $parameter), $violations[0]->getPropertyPath());
        if (!empty($message)) {
            $this->assertEquals($message, $violations[0]->getMessage());
        }
    }

    /**
     * @param string $parameter
     */
    public function assertCommandParameterIsMandatory($parameter)
    {
        $parameters = $this->getParameters();
        unset($parameters[$parameter]);

        $violations = $this->getViolations($parameters);

        $this->assertEquals(1, $violations->count());
        $this->assertEquals(sprintf('[%s]', $parameter), $violations[0]->getPropertyPath());
        $this->assertEquals('This field is missing.', $violations[0]->getMessage());
    }

    /**
     * @param array $parameters
     */
    public function assertCommandParametersAreValid(array $parameters)
    {
        $violations = $this->getViolations($parameters);

        $this->assertEquals(0, $violations->count());
    }

    /**
     * @param string $parameter
     */
    public function assertCommandParameterIsNotDefined($parameter)
    {
        $parameters             = $this->getParameters();
        $parameters[$parameter] = '';

        $violations = $this->getViolations($parameters);

        $this->assertEquals(1, $violations->count());
        $this->assertEquals(sprintf('[%s]', $parameter), $violations[0]->getPropertyPath());
        $this->assertEquals('This field was not expected.', $violations[0]->getMessage());
    }
}