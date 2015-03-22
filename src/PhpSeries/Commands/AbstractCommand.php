<?php
namespace PhpSeries\Commands;

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Message\RequestInterface;
use PhpSeries\Exceptions\BetaSeriesException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractCommand
 *
 * @package PhpSeries
 */
abstract class AbstractCommand implements CommandInterface
{

    /**
     * @var ClientInterface
     */
    protected $httpClient;

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
     * @param ClientInterface $guzzleClient
     * @param string          $apiKey
     * @param string          $apiVersion
     * @param string          $userAgent
     */
    public function __construct(ClientInterface $guzzleClient, $apiKey, $apiVersion, $userAgent)
    {
        $this->httpClient = $guzzleClient;
        $this->apiKey     = $apiKey;
        $this->apiVersion = $apiVersion;
        $this->userAgent  = $userAgent;
    }

    /**
     * @param string $method
     * @param string $url
     *
     * @return RequestInterface
     */
    protected function getRequest($method, $url)
    {
        $headers = [
            'X-BetaSeries-Version' => $this->apiVersion,
            'X-BetaSeries-Key'     => $this->apiKey,
            'Accept'               => 'application/json',
            'User-Agent'           => $this->userAgent
        ];

        return $this->httpClient->createRequest(
            strtoupper($method),
            $url,
            $headers
        );
    }

    /**
     * @param array $parameters
     *
     * @return array
     */
    protected function resolveParameters(array $parameters)
    {
        $optionResolver = new OptionsResolver();

        return $optionResolver->setDefaults($this->getDefaultParameters())
            ->resolve($parameters);
    }

    /**
     * @param string $data
     *
     * @return array
     * @throws BetaSeriesException
     */
    protected function jsonDecode($data)
    {
        $exceptionMapping = [
            1 => 'ApiException',
            2 => 'UserException',
            3 => 'VariableException',
            4 => 'DatabaseException',
        ];

        $jsonData = json_decode($data, true);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                if (isset($jsonData['errors']['code'])) {
                    $errorType      = substr($jsonData['errors']['code'], 0, 1);
                    $exceptionClass = sprintf('\PhpSeries\Exceptions\%s', array_key_exists($errorType, $exceptionMapping) ? $exceptionMapping[$errorType] : 'BetaSeriesException');

                    throw new $exceptionClass($jsonData['errors']['text'], $jsonData['errors']['code']);
                }

                return $jsonData;
                break;
            case JSON_ERROR_DEPTH:
                throw new BetaSeriesException('Maximum stack depth exceeded');
                break;
            case JSON_ERROR_STATE_MISMATCH:
                throw new BetaSeriesException('Underflow or the modes mismatch');
                break;
            case JSON_ERROR_CTRL_CHAR:
                throw new BetaSeriesException('Unexpected control character found');
                break;
            case JSON_ERROR_SYNTAX:
                throw new BetaSeriesException('Syntax error, malformed JSON');
                break;
            case JSON_ERROR_UTF8:
                throw new BetaSeriesException('Malformed UTF-8 characters, possibly incorrectly encoded');
                break;
            default:
                throw new BetaSeriesException('Unknown error');
                break;
        }
    }

    /**
     * @return array
     */
    abstract protected function getDefaultParameters();

    /**
     * {@inheritdoc}
     */
    public function execute($method, $url, array $parameters)
    {
        $request    = $this->getRequest($method, $url);
        $parameters = $this->resolveParameters($parameters);

        foreach ($parameters as $key => $value) {
            $request->getParams()->add($key, $value);
        }

        $response = $request->send();

        if ($response->isError()) {
            throw new BetaSeriesException(sprintf('Http error %s', $response->getStatusCode()));
        }

        return $this->jsonDecode($response->getBody(true));
    }
}