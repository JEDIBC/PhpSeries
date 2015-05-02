<?php
namespace PhpSeries\Commands;

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Message\Response;
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
     * @param ClientInterface $httpClient
     * @param string          $apiKey
     * @param string          $apiVersion
     * @param string          $userAgent
     */
    public function __construct(ClientInterface $httpClient, $apiKey, $apiVersion = '2.4', $userAgent = 'PhpSeries')
    {
        $this->httpClient = $httpClient;
        $this->apiKey     = $apiKey;
        $this->apiVersion = $apiVersion;
        $this->userAgent  = $userAgent;
    }

    /**
     * @param OptionsResolver $resolver
     */
    abstract protected function configureParameters(OptionsResolver $resolver);

    /**
     * @param string $method
     * @param string $url
     * @param array  $parameters
     *
     * @return Response
     */
    protected function getHttpResponse($method, $url, array $parameters = [])
    {
        // define http method
        $method = strtoupper($method);

        // Resolve parameters
        $resolver = new OptionsResolver();
        $this->configureParameters($resolver);

        // Filter resolved parameters to keep only non empty ones
        $parameters = array_filter(
            $resolver->resolve($parameters),
            function ($value) {
                return is_array($value) ? !empty($value) : '' != trim((string) $value);
            }
        );

        // build url
        $url = 'GET' == $method ? sprintf('%s?%s', $url, http_build_query($parameters)) : $url;

        // build body
        $body = 'GET' == $method ? null : $parameters;

        // build headers
        $headers = [
            'X-BetaSeries-Version' => $this->apiVersion,
            'X-BetaSeries-Key'     => $this->apiKey,
            'Accept'               => 'application/json',
            'User-Agent'           => $this->userAgent
        ];

        // Execute query
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

        $jsonData = @json_decode($data, true);

        if (JSON_ERROR_NONE === json_last_error()) {
            if (isset($jsonData['errors'][0]['code'])) {
                $errorType      = substr($jsonData['errors'][0]['code'], 0, 1);
                $exceptionClass = sprintf('\PhpSeries\Exceptions\%s', array_key_exists($errorType, $exceptionMapping) ? $exceptionMapping[$errorType] : 'BetaSeriesException');

                throw new $exceptionClass($jsonData['errors'][0]['text'], $jsonData['errors'][0]['code']);
            }

            return $jsonData;
        } else {
            $msg = json_last_error_msg();
            throw new BetaSeriesException(empty($msg) ? 'Unknown json error' : $msg);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute($method, $url, array $parameters = [])
    {
        $response = $this->getHttpResponse($method, $url, $parameters);

        if ($response->isError()) {
            throw new BetaSeriesException(sprintf('Http error %d', $response->getStatusCode()));
        }

        if (empty($response->getBody(true))) {
            throw new BetaSeriesException('Empty response');
        }

        return $this->jsonDecode($response->getBody(true));
    }
}