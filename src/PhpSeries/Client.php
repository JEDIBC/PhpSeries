<?php
namespace PhpSeries;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Class Client
 *
 * @package PhpSeries
 */
class Client
{
    /**
     * @var GuzzleClientInterface
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $headers;

    /**
     * @param GuzzleClientInterface $httpClient
     * @param array                 $parameters
     *      - api-version : Toutes les requêtes doivent avoir le paramètre de
     *      version au moins égal à 2.0 pour utiliser cette API
     *      - api-key     : Pour toutes vos requêtes, vous devez mentionner
     *      votre clé API dans la requête
     *      - user-agent  : Il est recommandé de spécifier un User-Agent
     *      spécifique à votre application dans vos requêtes. De ce fait, si
     *      vous utilisez votre clé pour plusieurs applications, il nous sera
     *      plus facile de différencer votre trafic.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(GuzzleClientInterface $httpClient, array $parameters = [])
    {

        if (!array_key_exists('api-version', $parameters)) {
            throw new \InvalidArgumentException('Missing api-version parameter');
        }

        if (!array_key_exists('api-key', $parameters)) {
            throw new \InvalidArgumentException('Missing api-key parameter');
        }

        if (!array_key_exists('user-agent', $parameters)) {
            throw new \InvalidArgumentException('Missing user-agent parameter');
        }

        $this->httpClient = $httpClient;
        $this->headers    = [
            'X-BetaSeries-Version' => $parameters['api-version'],
            'X-BetaSeries-Key'     => $parameters['api-key'],
            'User-Agent'           => $parameters['user-agent'],
            'Accept'               => 'application/json'
        ];
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $params
     *
     * @return array
     */
    protected function query($method, $url, $params = [])
    {
        if ('get' === $method) {
            $options = [
                'headers' => $this->headers,
                'query'   => $params
            ];
        } else {
            $options = [
                'headers' => $this->headers,
                'body'    => $params
            ];
        }

        $request = $this->httpClient->createRequest($method, $url, $options);

        return $this->handleJsonResponse($this->httpClient->send($request));
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @return array
     */
    protected function get($url, $params = [])
    {
        return $this->query('get', $url, $params);
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @return array
     */
    protected function post($url, $params = [])
    {
        return $this->query('post', $url, $params);
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @return array
     */
    protected function put($url, $params = [])
    {
        return $this->query('put', $url, $params);
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @return array
     */
    protected function delete($url, $params = [])
    {
        return $this->query('delete', $url, $params);
    }

    /**
     * @param Response $response
     *
     * @throws \GuzzleHttp\Exception\BadResponseException
     * @throws \HttpResponseException
     * @return array
     */
    protected function handleJsonResponse(Response $response)
    {
        if (200 !== $response->getStatusCode()) {
            throw new \HttpResponseException("Wrong status code", $response->getStatusCode());
        }

        $jsonData = json_decode($response->getBody(), true);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $jsonData;
                break;
            case JSON_ERROR_DEPTH:
                throw new BadResponseException('Maximum stack depth exceeded');
                break;
            case JSON_ERROR_STATE_MISMATCH:
                throw new BadResponseException('Underflow or the modes mismatch');
                break;
            case JSON_ERROR_CTRL_CHAR:
                throw new BadResponseException('Unexpected control character found');
                break;
            case JSON_ERROR_SYNTAX:
                throw new BadResponseException('Syntax error, malformed JSON');
                break;
            case JSON_ERROR_UTF8:
                throw new BadResponseException('Malformed UTF-8 characters, possibly incorrectly encoded');
                break;
            default:
                throw new BadResponseException('Unknown error');
                break;
        }
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function setToken($token)
    {
        $this->headers['X-BetaSeries-Token'] = $token;

        return $this;
    }

    /**
     * Identification classique du membre.
     *
     * @param string $login
     * @param string $md5Password
     *
     * @return array
     */
    public function membersAuth($login, $md5Password)
    {
        return $this->post(
            '/members/auth',
            [
                'login'    => $login,
                'password' => $md5Password
            ]
        );
    }

}