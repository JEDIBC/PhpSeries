<?php
namespace PhpSeries;

use Guzzle\Http\Client as HttpClient;
use Guzzle\Http\ClientInterface;
use PhpSeries\Commands\AbstractCommand;
use PhpSeries\Exceptions\BetaSeriesException;
use Psr\Log\LoggerInterface;

/**
 * Class Client
 *
 * @package PhpSeries
 */
class Client
{

    /**
     * @var string
     */
    protected $host = "https://api.betaseries.com";

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $apiVersion;

    /**
     * @var string
     */
    protected $userAgent;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @param string $apiKey
     * @param string $apiVersion
     * @param string $userAgent
     */
    public function __construct($apiKey, $apiVersion = '2.4', $userAgent = 'PhpSeries')
    {
        $this->apiKey     = $apiKey;
        $this->apiVersion = $apiVersion;
        $this->userAgent  = $userAgent;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     *
     * @return Client
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * @param string $apiKey
     *
     * @return Client
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @param string $apiVersion
     *
     * @return Client
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     *
     * @return Client
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return Client
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return ClientInterface
     */
    public function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new HttpClient();
        }

        return $this->httpClient;
    }

    /**
     * @param ClientInterface $httpClient
     *
     * @return Client
     */
    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @param string $method
     * @param string $category
     * @param string $action
     *
     * @return string
     */
    protected function getClassName($method, $category, $action)
    {
        return sprintf('\PhpSeries\Commands\%s\%sCommand', ucfirst($category), $method . ucfirst($action));
    }

    /**
     * @param string $method
     * @param string $apiMethod
     * @param array  $parameters
     *
     * @return mixed
     * @throws BetaSeriesException
     */
    protected function executeCommand($method, $apiMethod, array $parameters = [])
    {
        list($category, $action) = explode('/', strtolower($apiMethod));
        $className = $this->getClassName($method, $category, $action);

        if (!class_exists($className)) {
            throw new BetaSeriesException(sprintf("The API command %s %s doesn't exist", $method, $apiMethod));
        }

        /* @var $command AbstractCommand */
        $command = new $className($this->getHttpClient(), $this->apiKey, $this->apiVersion, $this->userAgent);

        return $command->execute($method, sprintf('%s/%s', $this->host, $apiMethod), $parameters);
    }

    /**
     * @param string $apiMethod
     * @param array  $parameters
     *
     * @return mixed
     */
    public function get($apiMethod, array $parameters = [])
    {
        return $this->executeCommand('Get', $apiMethod, $parameters);
    }

    /**
     * @param string $apiMethod
     * @param array  $parameters
     *
     * @return mixed
     */
    public function post($apiMethod, array $parameters = [])
    {
        return $this->executeCommand('Post', $apiMethod, $parameters);
    }

    /**
     * @param string $apiMethod
     * @param array  $parameters
     *
     * @return mixed
     */
    public function delete($apiMethod, array $parameters = [])
    {
        return $this->executeCommand('Delete', $apiMethod, $parameters);
    }
}