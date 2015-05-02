<?php
namespace Tests\PhpSeries;

use Mockery as m;
use PhpSeries\Client;

/**
 * Class ClientTest
 *
 * @package Tests\PhpSeries
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
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
    public function testGettersSetters()
    {
        $client = new Client('foo', 'bar', 'gru');

        $this->assertEquals('foo', $client->getApiKey());
        $this->assertEquals('bar', $client->getApiVersion());
        $this->assertEquals('gru', $client->getUserAgent());
        $this->assertEquals('https://api.betaseries.com', $client->getHost());

        $this->assertEquals('apiKey', $client->setApiKey('apiKey')->getApiKey());
        $this->assertEquals('apiVersion', $client->setApiVersion('apiVersion')->getApiVersion());
        $this->assertEquals('userAgent', $client->setUserAgent('userAgent')->getUserAgent());
        $this->assertEquals('http://perdu.com', $client->setHost('http://perdu.com')->getHost());

        $this->assertInstanceOf('Guzzle\Http\Client', $client->getHttpClient());
        $httpClientMock = m::mock('Guzzle\Http\ClientInterface');
        $this->assertNotEquals($httpClientMock, $client->getHttpClient());
        $this->assertEquals($httpClientMock, $client->setHttpClient($httpClientMock)->getHttpClient());
    }

    /**
     * @test
     */
    public function testGet()
    {
        $apiMethod  = 'foo/bar';
        $parameters = ['foo' => 'bar'];

        $clientMock = m::mock('PhpSeries\Client', ['dummyKey'])->shouldAllowMockingProtectedMethods()->shouldDeferMissing();
        $clientMock->shouldReceive('executeCommand')->once()->with('GET', $apiMethod, $parameters);

        $clientMock->get($apiMethod, $parameters);
    }

    /**
     * @test
     */
    public function testPost()
    {
        $apiMethod  = 'foo/bar';
        $parameters = ['foo' => 'bar'];

        $clientMock = m::mock('PhpSeries\Client', ['dummyKey'])->shouldAllowMockingProtectedMethods()->shouldDeferMissing();
        $clientMock->shouldReceive('executeCommand')->once()->with('POST', $apiMethod, $parameters);

        $clientMock->post($apiMethod, $parameters);
    }

    /**
     * @test
     */
    public function testDelete()
    {
        $apiMethod  = 'foo/bar';
        $parameters = ['foo' => 'bar'];

        $clientMock = m::mock('PhpSeries\Client', ['dummyKey'])->shouldAllowMockingProtectedMethods()->shouldDeferMissing();
        $clientMock->shouldReceive('executeCommand')->once()->with('DELETE', $apiMethod, $parameters);

        $clientMock->delete($apiMethod, $parameters);
    }

    /**
     * @test
     * @expectedException \PhpSeries\Exceptions\BetaSeriesException
     * @expectedExceptionMessage The API command Get foo/bar doesn't exist
     */
    public function testExecuteCommandDoesntExist()
    {
        $client = new Client('dummyKey');
        $client->get('foo/bar');
    }

    /**
     * @test
     */
    public function testExecuteCommand()
    {
        $clientMock = m::mock('PhpSeries\Client[getClassName]', ['dummyKey'])->shouldAllowMockingProtectedMethods();
        $clientMock->shouldReceive('getClassName')->once()->andReturn('Tests\PhpSeries\DummyCOmmand');

        $this->assertEquals(['foo' => 'bar'], $clientMock->get('foo/bar'));
    }

    /**
     * @test
     */
    public function testGetClassName()
    {
        $clientMock = m::mock('PhpSeries\Client', ['dummyKey'])->shouldDeferMissing();

        $this->assertEquals('\PhpSeries\Commands\Foo\GetBarCommand', $clientMock->getClassName('Get', 'Foo', 'Bar'));
    }


}