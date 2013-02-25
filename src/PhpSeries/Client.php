<?php
namespace PhpSeries;

use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Exception\BadResponseException;
use Monolog\Logger;
use Guzzle\Log\MonologLogAdapter;
use Guzzle\Plugin\Log\LogPlugin;
use Guzzle\Log\MessageFormatter;

class Client
{

    /**
     * @var string
     */
    protected $host = "http://api.betaseries.com";

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var GuzzleClient
     */
    protected $guzzleClient;

    /**
     * @var string
     */
    protected $userAgent;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param string $apiKey
     * @param string $userAgent
     */
    public function __construct($apiKey, $userAgent = "BetaSeriesPHPClient")
    {
        $this->apiKey    = $apiKey;
        $this->userAgent = $userAgent;
    }

    /**
     * @param string$host
     * @return Client
     */
    public function setHost($host)
    {
        $this->guzzleClient = null;
        $this->host         = $host;

        return $this;
    }

    /**
     * @param string $userAgent
     * @return Client
     */
    public function setUserAgent($userAgent)
    {
        $this->guzzleClient = null;
        $this->userAgent    = $userAgent;

        return $this;
    }

    /**
     * @param Logger $logger
     * @return Client
     */
    public function setLogger(Logger $logger)
    {
        $this->guzzleClient = null;
        $this->logger       = $logger;

        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @return GuzzleClient
     */
    protected function getGuzzleClient()
    {
        if (is_null($this->guzzleClient)) {
            $this->guzzleClient = new GuzzleClient($this->host);
            $this->guzzleClient->setDefaultHeaders(array('User-Agent' => $this->userAgent));
            if (!is_null($this->logger)) {
                $this->guzzleClient->addSubscriber(
                    new LogPlugin(
                        new MonologLogAdapter($this->logger),
                        MessageFormatter::DEBUG_FORMAT
                    )
                );
            }
        }

        return $this->guzzleClient;
    }

    /**
     * @param string $url
     * @param array  $params
     * @return array
     */
    protected function query($url, $params = array())
    {
        $params = array_merge(array('key' => $this->apiKey), $params);

        return $this->handleJsonResponse($this->getGuzzleClient()->get($url . '?' . http_build_query($params))->send());
    }

    /**
     * @param \Guzzle\Http\Message\Response $response
     * @return mixed
     * @throws \Guzzle\Http\Exception\BadResponseException
     * @throws \HttpResponseException
     */
    protected function handleJsonResponse(Response $response)
    {
        if (200 !== $response->getStatusCode()) {
            throw new \HttpResponseException("Wrong status code", $response->getStatusCode());
        }

        $jsonData = json_decode($response->getBody(true), true);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                if (isset($jsonData['root'])) {
                    return $jsonData['root'];
                } else {
                    throw new BadResponseException('No root found');
                }
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
     * Pour savoir les dernières modifications des fonctions et le statut global de BetaSeries.
     *
     * @return array
     */
    public function getApiStatus()
    {
        return $this->query('status.json');
    }

    /**
     * Liste les séries qui contiennent exactement la portion search dans leur titre.
     *
     * @param string $title
     * @return array
     */
    public function showsSearch($title)
    {
        return $this->query('shows/search.json', array('title' => $title));
    }

    /**
     * Donne des informations sur la série (identifiée par son url).
     * Note : Si url est à all, la fonction retourne toutes les séries de BetaSeries.
     * Note : Si url est à random, la fonction retourne une série au hasard de BetaSeries.
     *
     * @param string $url
     * @return array
     */
    public function showsDisplay($url)
    {
        return $this->query('shows/display/' . $url . '.json');
    }

    /**
     * Liste les épisodes d'une série donnée. Vous pouvez préciser une saison (et un épisode) en paramètre.
     * Si le paramètre summary est spécifié, la description et la capture d'écran de l'épisode ne seront pas retournées.
     * Si le paramètre hide_notes est spécifié, les notes des épisodes ne seront pas retournées.
     * ATTENTION : Cette méthode peut prendre beaucoup de temps à charger si vous ne spécifiez pas de saison et d'épisode. Dans ces cas nous vous conseillons fortement d'utiliser au moins le paramètre hide_notes.
     * Note : Si vous spécifiez un token, vous saurez si le membre identifié a vu cet épisode ou non.
     *
     * @param string      $url
     * @param null|int    $season
     * @param null|int    $episode
     * @param bool|int    $summary
     * @param bool        $hide_notes
     * @param null|string $token
     * @return array
     * @throws \InvalidArgumentException
     */
    public function showsEpisodes($url, $season = null, $episode = null, $summary = false, $hide_notes = false, $token = null)
    {
        $params = array(
            'summary'    => (int)$summary,
            'hide_notes' => (int)$hide_notes
        );

        // handle season parameter
        if (!is_null($season)) {
            if (!ctype_digit($season)) {
                throw new \InvalidArgumentException("Invalid season");
            }
            $params['season'] = $season;
        }

        // Handle episode parameter
        if (!is_null($episode)) {
            if (!ctype_digit($episode)) {
                throw new \InvalidArgumentException("Invalid episode");
            }
            if (is_null($season)) {
                throw new \InvalidArgumentException('season not specified');
            }
            $params['episode'] = $episode;
        }

        // handle token parameter
        if (!is_null($token)) {
            $params['token'] = $token;
        }

        return $this->query('shows/episodes/' . $url . '.json', $params);
    }

    /**
     * Ajoute la série dans le compte du membre identifié.
     *
     * @param string $url
     * @param string $token
     * @return array
     */
    public function showsAdd($url, $token)
    {
        return $this->query('shows/add/' . $url . '.json', array('token' => $token));
    }

    /**
     * Retire la série du compte du membre identifié.
     *
     * @param string $url
     * @param string $token
     * @return array
     */
    public function showsRemove($url, $token)
    {
        return $this->query('shows/remove/' . $url . '.json', array('token' => $token));
    }

    /**
     * Recommande une série à un des amis de l'utilisateur identifié.
     *
     * @param string $url
     * @param string $token
     * @param string $friend
     * @return array
     */
    public function showsRecommend($url, $token, $friend)
    {
        return $this->query(
            'shows/recommend/' . $url . '.json',
            array(
                'token'  => $token,
                'friend' => $friend
            )
        );
    }

    /**
     * Archive une série pour l'utilisateur identifié.
     *
     * @param string $url
     * @param string $token
     * @return array
     */
    public function showsArchive($url, $token)
    {
        return $this->query('shows/archive/' . $url . '.json', array('token' => $token));
    }

    /**
     * Lance le scraper sur un nom de fichier de votre choix, pour en déduire la série, l'ID, le numéro de l'épisode.
     *
     * @param string $file
     * @return array
     */
    public function showsScrapper($file)
    {
        return $this->query('shows/scraper.json', array('file' => $file));
    }

    /**
     * Sort des archives une série pour l'utilisateur identifié.
     *
     * @param string $url
     * @param string $token
     * @return array
     */
    public function showsUnarchive($url, $token)
    {
        return $this->query('shows/unarchive/' . $url . '.json', array('token' => $token));
    }

    /**
     * Affiche les fiches personnages pour la série spécifiée.
     * Si vous spécifiez summary, seuls l'id et le nom du personnage seront renvoyés.
     * Vous pouvez ne demander qu'une seule fiche personnage en spécifiant son id.
     *
     * @param string $url
     * @param bool   $summary
     * @param int    $id
     * @return array
     */
    public function showsCharacters($url, $summary = false, $id = null)
    {
        $params = array(
            'summary' => (int)$summary
        );
        if (!is_null($id)) {
            $params['id'] = $id;
        }

        return $this->query('shows/characters/' . $url . '.json', $params);
    }

    /**
     * Retourne les séries similaires à la série soumise.
     *
     * @param string $url
     * @return array
     */
    public function showsSimilar($url)
    {
        return $this->query('shows/similar/' . $url . '.json');
    }

    /**
     * Retourne les vidéos associées à la séries soumise.
     * Si le paramètre season est spécifié, seules les vidéos associées à la série soumise et à la saison seront retournées.
     * Si les paramètres season et episode sont spécifiés, seules les vidéos associées à la série soumise et à la saison et à l'épisode seront retournées.
     *
     * @param string   $url
     * @param null|int $season
     * @param null|int $episode
     * @return array
     * @throws \InvalidArgumentException
     */
    public function showsVideos($url, $season = null, $episode = null)
    {
        $params = array();

        // handle season parameter
        if (!is_null($season)) {
            if (!ctype_digit($season)) {
                throw new \InvalidArgumentException("Invalid season");
            }
            $params['season'] = $season;
        }

        // Handle episode parameter
        if (!is_null($episode)) {
            if (!ctype_digit($episode)) {
                throw new \InvalidArgumentException("Invalid episode");
            }
            if (is_null($season)) {
                throw new \InvalidArgumentException('season not specified');
            }
            $params['episode'] = $episode;
        }

        return $this->query('shows/videos/' . $url . '.json', $params);
    }

    /**
     * Affiche les derniers sous-titres récupérés par BetaSeries, dans la limite de 100.
     * Possibilité de spécifier la langue et/ou une série en particulier.
     *
     * @param null|string $language
     * @param null|int    $number
     * @return array
     * @throws \InvalidArgumentException
     */
    public function subtitlesLast($language = null, $number = null)
    {
        $params = array();

        // handle language parameter
        if (!is_null($language)) {
            if (!in_array($language, array('vo', 'vf'))) {
                throw new \InvalidArgumentException("Language must be 'vo' or 'vf'");
            }
            $params['language'] = $language;
        }

        // handle number parameter
        if (!is_null($number)) {
            if (!ctype_digit($number)) {
                throw new \InvalidArgumentException("Invalid number");
            }
            $params['number'] = $number;
        }

        return $this->query('subtitles/last.json', $params);
    }

    /**
     * Affiche les sous-titres récupérés par BetaSeries d'une certaine série, dans la limite de 100.
     * Possibilité de spécifier la langue et/ou une saison, un épisode en particulier.
     *
     * @param string      $url
     * @param null|string $language
     * @param null|int    $season
     * @param null|int    $episode
     * @return array
     * @throws \InvalidArgumentException
     */
    public function subtitlesShow($url, $language = null, $season = null, $episode = null)
    {
        $params = array();

        // handle language parameter
        if (!is_null($language)) {
            if (!in_array($language, array('vo', 'vf'))) {
                throw new \InvalidArgumentException("Language must be 'vo' or 'vf'");
            }
            $params['language'] = $language;
        }

        // handle season parameter
        if (!is_null($season)) {
            if (!ctype_digit($season)) {
                throw new \InvalidArgumentException("Invalid season");
            }
            $params['season'] = $season;
        }

        // Handle episode parameter
        if (!is_null($episode)) {
            if (!ctype_digit($episode)) {
                throw new \InvalidArgumentException("Invalid episode");
            }
            if (is_null($season)) {
                throw new \InvalidArgumentException('season not specified');
            }
            $params['episode'] = $episode;
        }

        return $this->query('subtitles/show/' . $url . '.json', $params);
    }

    /**
     * Nouveau : Vous pouvez maintenant récupérer des sous-titres directement grâce au nom des fichiers vidéo
     *
     * @param string      $file
     * @param null|string $language
     * @return array
     * @throws \InvalidArgumentException
     */
    public function subtitlesShowByFile($file, $language = null)
    {
        $params = array(
            'file' => $file
        );

        // handle language parameter
        if (!is_null($language)) {
            if (!in_array($language, array('vo', 'vf'))) {
                throw new \InvalidArgumentException("Language must be 'vo' or 'vf'");
            }
            $params['language'] = $language;
        }

        return $this->query('subtitles/show.json', $params);
    }
}