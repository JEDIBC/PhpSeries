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
        $this->assertNull($client->getLogger());

        $this->assertEquals('apiKey', $client->setApiKey('apiKey')->getApiKey());
        $this->assertEquals('apiVersion', $client->setApiVersion('apiVersion')->getApiVersion());
        $this->assertEquals('userAgent', $client->setUserAgent('userAgent')->getUserAgent());
        $this->assertEquals('http://perdu.com', $client->setHost('http://perdu.com')->getHost());
        $this->assertInstanceOf('Psr\Log\LoggerInterface', $client->setLogger(m::mock('Psr\Log\LoggerInterface'))->getLogger());

        $this->assertInstanceOf('Guzzle\Http\Client', $client->getHttpClient());
        $httpClientMock = m::mock('Guzzle\Http\ClientInterface');
        $this->assertNotEquals($httpClientMock, $client->getHttpClient());
        $this->assertEquals($httpClientMock, $client->setHttpClient($httpClientMock)->getHttpClient());
    }
}