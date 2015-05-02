<?php
namespace Tests\PhpSeries\Commands;

use Mockery as m;

/**
 * Class AbstractCommandTest
 *
 * @package Tests\PhpSeries\Commands
 */
class AbstractCommandTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var m\MockInterface
     */
    protected $httpClientMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->httpClientMock = m::mock('Guzzle\Http\ClientInterface');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        m::close();
    }

    /**
     * @test
     */
    public function testExecute()
    {
        $requestMock = m::mock('Guzzle\Http\Message\Request');

        $responsetMock = m::mock('Guzzle\Http\Message\Response');

        $this->httpClientMock
            ->shouldReceive('createRequest')
            ->once()
            ->with(
                'POST',
                'http://foo.bar/gru',
                [
                    'X-BetaSeries-Version' => '2.4',
                    'X-BetaSeries-Key'     => 'dummyKey',
                    'Accept'               => 'application/json',
                    'User-Agent'           => 'PhpSeries'
                ],
                [
                    'foo' => 'bar'
                ],
                [
                    'exceptions' => false
                ]
            )->andReturn($requestMock);

        $requestMock->shouldReceive('send')->once()->andReturn($responsetMock);

        $responsetMock->shouldReceive('isError')->once()->andReturn(false);
        $responsetMock->shouldReceive('getBody')->twice()->with(true)->andReturn('{ "show": 20 }');

        $command = new TestCommand($this->httpClientMock, 'dummyKey');
        $data    = $command->execute(
            'post',
            'http://foo.bar/gru',
            [
                'foo' => 'bar',
                'gru' => ''
            ]
        );

        $this->assertEquals(['show' => 20], $data);
    }

    /**
     * @test
     * @expectedException \PhpSeries\Exceptions\BetaSeriesException
     * @expectedExceptionMessage Http error 404
     */
    public function testExecuteIsError()
    {
        $responsetMock = m::mock('Guzzle\Http\Message\Response');
        $responsetMock->shouldReceive('isError')->once()->andReturn(true);
        $responsetMock->shouldReceive('getStatusCode')->once()->andReturn(404);

        $commandMock = m::mock('PhpSeries\Commands\AbstractCommand[getHttpResponse]', [$this->httpClientMock, 'dummyKey'])->shouldAllowMockingProtectedMethods();
        $commandMock->shouldReceive('getHttpResponse')->once()->andReturn($responsetMock);

        $commandMock->execute('get', 'http://foo.bar/gru');
    }

    /**
     * @test
     * @expectedException \PhpSeries\Exceptions\BetaSeriesException
     * @expectedExceptionMessage Empty response
     */
    public function testExecuteResponseIsEmpty()
    {
        $responsetMock = m::mock('Guzzle\Http\Message\Response');
        $responsetMock->shouldReceive('isError')->once()->andReturn(false);
        $responsetMock->shouldReceive('getBody')->once()->with(true)->andReturn('');

        $commandMock = m::mock('PhpSeries\Commands\AbstractCommand[getHttpResponse]', [$this->httpClientMock, 'dummyKey'])->shouldAllowMockingProtectedMethods();
        $commandMock->shouldReceive('getHttpResponse')->once()->andReturn($responsetMock);

        $commandMock->execute('get', 'http://foo.bar/gru');
    }

    /**
     * @test
     * @expectedException \PhpSeries\Exceptions\ApiException
     * @expectedExceptionMessage foobar
     */
    public function testJsonResponseApiError()
    {
        $commandMock = m::mock('PhpSeries\Commands\AbstractCommand', [$this->httpClientMock, 'dummyKey'])->shouldDeferMissing();
        $commandMock->jsonDecode('{ "errors": [ { "code": 1001, "text": "foobar" } ] }');
    }

    /**
     * @test
     * @expectedException \PhpSeries\Exceptions\BetaSeriesException
     * @expectedExceptionMessage Syntax error
     */
    public function testJsonResponseDecodeError()
    {
        $commandMock = m::mock('PhpSeries\Commands\AbstractCommand', [$this->httpClientMock, 'dummyKey'])->shouldDeferMissing();
        $commandMock->jsonDecode('qsmdlkfjqmlsdkfgj');
    }
}