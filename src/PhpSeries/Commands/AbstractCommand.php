<?php
namespace PhpSeries\Commands;

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Message\Response;
use PhpSeries\Exceptions\BetaSeriesException;

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
     * @return array
     */
    abstract protected function resolveParameters(array $parameters);

    /**
     * @param string $method
     * @param string $url
     * @param array  $parameters
     *
     * @return Response
     */
    protected function getHttpResponse($method, $url, array $parameters = [])
    {
        $method     = strtoupper($method);
        $parameters = array_filter(
            $this->resolveParameters($parameters),
            function ($value) {
                return is_array($value) ? !empty($value) : '' != trim((string) $value);
            }
        );
        $url        = 'GET' == $method ? sprintf('%s?%s', $url, http_build_query($parameters)) : $url;
        $body       = 'GET' == $method ? null : $parameters;
        $headers    = [
            'X-BetaSeries-Version' => $this->apiVersion,
            'X-BetaSeries-Key'     => $this->apiKey,
            'Accept'               => 'application/json',
            'User-Agent'           => $this->userAgent
        ];

        return $this->httpClient
            ->createRequest(
                $method,
                $url,
                $headers,
                $body,
                ['exceptions' => false]
            )->send();
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
                if (isset($jsonData['errors'][0]['code'])) {
                    $errorType      = substr($jsonData['errors'][0]['code'], 0, 1);
                    $exceptionClass = sprintf('\PhpSeries\Exceptions\%s', array_key_exists($errorType, $exceptionMapping) ? $exceptionMapping[$errorType] : 'BetaSeriesException');

                    throw new $exceptionClass($jsonData['errors'][0]['text'], $jsonData['errors'][0]['code']);
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
     * {@inheritdoc}
     */
    public function execute($method, $url, array $parameters)
    {
        $response = $this->getHttpResponse($method, $url, $parameters);

        if ($response->isError() && empty($response->getBody(true))) {
            throw new BetaSeriesException(sprintf('Http error %s', $response->getStatusCode()));
        }

        return $this->jsonDecode($response->getBody(true));
    }
}