<?php
namespace Tests\PhpSeries\Commands;

use Guzzle\Http\Client;
use PhpSeries\Commands\AbstractCommand;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
     * @param string $parameter
     * @param mixed  $value
     */
    public function assertCommandParameterHasBadType($parameter, $value)
    {
        $resolver = new OptionsResolver();
        $command  = $this->getCommand();

        // Use reflexion to call configureParameters method on command
        $reflexionObject = new \ReflectionObject($command);
        $reflexionMethod = $reflexionObject->getMethod('configureParameters');
        $reflexionMethod->setAccessible(true);
        $reflexionMethod->invokeArgs($command, [$resolver]);

        $parameters             = $this->getParameters();
        $parameters[$parameter] = $value;

        try {
            $resolver->resolve($parameters);
            $this->fail('Exception should be thrown');
        } catch (\Exception $e) {
            $this->assertInstanceOf('Symfony\Component\OptionsResolver\Exception\InvalidOptionsException', $e);
            $this->assertTrue(false !== strpos($e->getMessage(), sprintf('"%s"', $parameter)));
        }
    }

    /**
     * @param string $parameter
     */
    public function assertCommandParameterIsMandatory($parameter)
    {
        $resolver = new OptionsResolver();
        $command  = $this->getCommand();

        // Use reflexion to call configureParameters method on command
        $reflexionObject = new \ReflectionObject($command);
        $reflexionMethod = $reflexionObject->getMethod('configureParameters');
        $reflexionMethod->setAccessible(true);
        $reflexionMethod->invokeArgs($command, [$resolver]);

        $parameters = $this->getParameters();
        unset($parameters[$parameter]);

        try {
            $resolver->resolve($parameters);
            $this->fail('Exception should be thrown');
        } catch (\Exception $e) {
            $this->assertInstanceOf('Symfony\Component\OptionsResolver\Exception\MissingOptionsException', $e);
            $this->assertTrue(false !== strpos($e->getMessage(), sprintf('"%s"', $parameter)));
        }
    }

    /**
     * @param array $parameters
     */
    public function assertCommandParametersAreValid(array $parameters)
    {
        $resolver = new OptionsResolver();
        $command  = $this->getCommand();

        // Use reflexion to call configureParameters method on command
        $reflexionObject = new \ReflectionObject($command);
        $reflexionMethod = $reflexionObject->getMethod('configureParameters');
        $reflexionMethod->setAccessible(true);
        $reflexionMethod->invokeArgs($command, [$resolver]);

        try {
            $this->assertEquals($parameters, $resolver->resolve($parameters));
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @param string $parameter
     */
    public function assertCommandParameterIsNotDefined($parameter)
    {
        $resolver = new OptionsResolver();
        $command  = $this->getCommand();

        // Use reflexion to call configureParameters method on command
        $reflexionObject = new \ReflectionObject($command);
        $reflexionMethod = $reflexionObject->getMethod('configureParameters');
        $reflexionMethod->setAccessible(true);
        $reflexionMethod->invokeArgs($command, [$resolver]);

        $parameters             = $this->getParameters();
        $parameters[$parameter] = '';

        try {
            $resolver->resolve($parameters);
            $this->fail('Exception should be thrown');
        } catch (\Exception $e) {
            $this->assertInstanceOf('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException', $e);
            $this->assertTrue(false !== strpos($e->getMessage(), sprintf('"%s"', $parameter)));
        }
    }
}