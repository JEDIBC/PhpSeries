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
    public function __construct($apiKey, $userAgent = "PhpSeries")
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
     * @param string $method
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function query($url, $params = array(), $method = 'get')
    {
        $params = array_merge(array('key' => $this->apiKey), $params);

        if ('get' === $method) {
            return $this->handleJsonResponse($this->getGuzzleClient()->get($url . '?' . http_build_query($params))->send());
        } else if ('post' === $method) {
            return $this->handleJsonResponse($this->getGuzzleClient()->post($url, null, $params)->send());
        } else {
            throw new \InvalidArgumentException('Invalid method parameter');
        }
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
                throw new \InvalidArgumentException("Invalid season parameter");
            }
            $params['season'] = $season;
        }

        // Handle episode parameter
        if (!is_null($episode)) {
            if (!ctype_digit($episode)) {
                throw new \InvalidArgumentException("Invalid episode parameter");
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
                throw new \InvalidArgumentException("Invalid season parameter");
            }
            $params['season'] = $season;
        }

        // Handle episode parameter
        if (!is_null($episode)) {
            if (!ctype_digit($episode)) {
                throw new \InvalidArgumentException("Invalid episode parameter");
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
                throw new \InvalidArgumentException("Invalid number parameter");
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
                throw new \InvalidArgumentException("Invalid season parameter");
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

    /**
     * Affiche tous les épisodes diffusés les 8 derniers jours jusqu'aux 8 prochains jours.
     *
     * @return array
     */
    public function planningGeneral()
    {
        return $this->query('planning/general.json');
    }

    /**
     * Affiche le planning du membre identifié ou d'un autre membre (l'accès varie selon les options vie privée de chaque membre).
     * Vous pouvez rajouter le paramètre view pour n'afficher que les épisodes encore non-vus.
     *
     * @param null|string $token
     * @param null|string $login
     * @param bool        $unseenOnly
     * @return array
     * @throws \InvalidArgumentException
     */
    public function planningMember($token = null, $login = null, $unseenOnly = false)
    {
        $url    = 'planning/member.json';
        $params = array();

        // Check params
        if (is_null($token) && is_null($login)) {
            throw new \InvalidArgumentException("You must specify token or login");
        }

        // handle login parameter
        if (!is_null($login)) {
            $url = 'planning/member/' . $login . '.json';
        }

        // handle token parameter
        if (!is_null($token)) {
            $params['token'] = $token;
        }

        // handle view parameter
        if (true === $unseenOnly) {
            $params['view'] = 'unseen';
        }

        return $this->query($url, $params);
    }

    /**
     * Identifie le membre avec son login et le hash MD5. Retourne le token à utiliser pour les requêtes futures.
     *
     * @param string $login
     * @param string $md5Password
     * @return array
     */
    public function membersAuth($login, $md5Password)
    {
        $params = array(
            'login'    => $login,
            'password' => $md5Password
        );

        return $this->query('members/auth.json', $params);
    }

    /**
     * Récupère une clé à utiliser en paramètre de https://www.betaseries.com/oauth?key=<key> pour identifier l'utilisateur sans avoir à envoyer un mot de passe sur l'API.
     * L'utilisateur est ensuite redirigé sur l'URL de callback que vous avez spécifiée.
     *
     * @param string $token
     * @return array
     */
    public function memberOauth($token)
    {
        return $this->query('members/oauth.json', array('token' => $token));
    }

    /**
     * Fonction sans action si ce n'est celle de vérifier si le token spécifié est actif.
     *
     * @param string $token
     * @return array
     */
    public function memberIsActive($token)
    {
        return $this->query('members/is_active.json', array('token' => $token));
    }

    /**
     * Détruit instantanément le token spécifié.
     *
     * @param string $token
     * @return array
     */
    public function memberDestroy($token)
    {
        return $this->query('members/destroy.json', array('token' => $token));
    }

    /**
     * Renvoie les informations principales du membre identifié ou d'un autre membre (l'accès varie selon les options vie privée de chaque membre).
     * Si vous spécifiez le paramètre nodata, seuls le login et la date du cache seront retournés.
     * Si vous spécifiez le paramètre since (valeur timestamp), l'API ne renverra les informations complètes que si elles ont été mises à jour depuis.
     *
     * @param null|string $token
     * @param null|string $login
     * @param bool        $nodata
     * @param null|int    $since
     * @return array
     * @throws \InvalidArgumentException
     */
    public function memberInfos($token = null, $login = null, $nodata = false, $since = null)
    {
        $url    = 'members/infos.json';
        $params = array();

        // Check params
        if (is_null($token) && is_null($login)) {
            throw new \InvalidArgumentException("You must specify token or login");
        }

        // handle login parameter
        if (!is_null($login)) {
            $url = 'members/infos/' . $login . '.json';
        }

        // handle token parameter
        if (!is_null($token)) {
            $params['token'] = $token;
        }

        // handle nodata parameter
        if (true === $nodata) {
            $params['nodata'] = 1;
        }

        // handle since parameter
        if (!is_null($since)) {
            if (!ctype_digit($since)) {
                throw new \InvalidArgumentException("Invalid since parameter");
            }
            $params['since'] = $since;
        }

        return $this->query($url, $params);
    }

    /**
     * Liste les épisodes restant à regarder du membre identifié.
     * Vous pouvez affiner par type de sous-titres : Tous (même les épisodes sans sous-titres), VF ou VF et VO.
     * En spécifiant view=next, l'API ne retourne que le premier épisode de chaque série à regarder.
     * Si vous spécifiez un nombre à view, les NN prochains épisodes seront retournés.
     * Si vous spécifiez show, les épisodes à voir de cette série uniquement seront retournés.
     *
     * @param string          $token
     * @param string          $subtitles
     * @param null|string     $show
     * @param null|string|int $view
     * @return array
     * @throws \InvalidArgumentException
     */
    public function membersEpisodes($token, $subtitles = 'all', $show = null, $view = null)
    {
        // handle sousTitres params
        if (!in_array($subtitles, array('all', 'vovf', 'vf'))) {
            throw new \InvalidArgumentException("Invalid subtitles parameter");
        }
        $url = 'members/episodes/' . $subtitles . '.json';

        $params = array(
            'token' => $token
        );

        // handle show parameter
        if (!is_null($show)) {
            $params['show'] = $show;
        }

        if (!is_null($view)) {
            if (('next' !== $view) || !ctype_digit($view)) {
                $params['view'] = $view;
            }
        }

        return $this->query($url, $params);
    }

    /**
     * Marque l'épisode episode de la saison season de la série url comme vu. Vous pouvez spécifier une note entre 1 et 5.
     * Note : Si l'épisode marqué comme vu ne suit pas directement le précédent épisode vu, tous les épisodes entre les deux seront quand même marqués comme vus. Pour mettre à zéro une série, marquez l'épisode 0 de la saison 0.
     *
     * @param string   $token
     * @param string   $url
     * @param int      $season
     * @param int      $episode
     * @param null|int $note
     * @return array
     * @throws \InvalidArgumentException
     */
    public function membersWatched($token, $url, $season, $episode, $note = null)
    {
        // handle season parameter
        if (!ctype_digit($season)) {
            throw new \InvalidArgumentException("Invalid season parameter");
        }

        // Handle episode parameter
        if (!ctype_digit($episode)) {
            throw new \InvalidArgumentException("Invalid episode parameter");
        }

        $params = array(
            'token'   => $token,
            'season'  => $season,
            'episode' => $episode
        );

        // handle note parameter
        if (!is_null($note)) {
            if (!in_array($note, array(1, 2, 3, 4, 5))) {
                throw new \InvalidArgumentException("Invalid note parameter");
            }
        }

        return $this->query('members/watched/' . $url . '.json', $params);
    }

    /**
     * Donne une note entre 1 et 5 à l'épisode episode de la saison season de la série url.
     * Note : Pour noter la série entière, il faut indiquer episode=0 et season=0.
     *
     * @param string $token
     * @param string $url
     * @param int    $season
     * @param int    $episode
     * @param int    $note
     * @return array
     * @throws \InvalidArgumentException
     */
    public function membersNote($token, $url, $season, $episode, $note)
    {
        // handle season parameter
        if (!ctype_digit($season)) {
            throw new \InvalidArgumentException("Invalid season parameter");
        }

        // Handle episode parameter
        if (!ctype_digit($episode)) {
            throw new \InvalidArgumentException("Invalid episode parameter");
        }

        // handle note parameter
        if (!in_array($note, array(1, 2, 3, 4, 5))) {
            throw new \InvalidArgumentException("Invalid note parameter");
        }

        $params = array(
            'token'   => $token,
            'season'  => $season,
            'episode' => $episode,
            'note'    => $note
        );

        return $this->query('members/note/' . $url . '.json', $params);
    }

    /**
     * Marque l'épisode episode de la saison season de la série url comme récupéré.
     *
     * @param string $token
     * @param string $url
     * @param int    $season
     * @param int    $episode
     * @return array
     * @throws \InvalidArgumentException
     */
    public function membersDownloaded($token, $url, $season, $episode)
    {
        // handle season parameter
        if (!ctype_digit($season)) {
            throw new \InvalidArgumentException("Invalid season parameter");
        }

        // Handle episode parameter
        if (!ctype_digit($episode)) {
            throw new \InvalidArgumentException("Invalid episode parameter");
        }

        $params = array(
            'token'   => $token,
            'season'  => $season,
            'episode' => $episode
        );

        return $this->query('members/downloaded/' . $url . '.json', $params);
    }

    /**
     * Afficher dans l'ordre chronologique les notifications reçues par le membre (nouveaux sous-titres, nouveaux épisodes sortis, etc.). En paramètres il peut être spécifié de commencer à partir d'un certain ID ou encore de limiter le nombre de résultats.
     * Nous vous conseillons de ne pas utiliser l'argument "sort" si vous utilisez déjà "last_id", vous risqueriez de ne pas récupérer toutes les dernières notifications.
     * Note : Si vous utilisez le paramètre "summary", l'API ne retournera que le nombre de notifications non-vues.
     * Note : Chaque notification retournée par l'API sera automatiquement marquée comme vue et supprimée.
     * Note : Le tri par défaut est ascendant (du plus vieux au plus récent).
     *
     * @param string      $token
     * @param bool        $summary
     * @param null|int    $number
     * @param null|int    $last_id
     * @param null|string $sort
     * @return array
     * @throws \InvalidArgumentException
     */
    public function membersNotifications($token, $summary = false, $number = null, $last_id = null, $sort = null)
    {
        $params = array(
            'token' => $token
        );

        // handle sumary parameter
        if (true === $summary) {
            $params['summary'] = 'yes';
        }

        // handle number parameter
        if (!is_null($number)) {
            if (!ctype_digit($number)) {
                throw new \InvalidArgumentException("Invalid number parameter");
            }
            $params['number'] = $number;
        }

        // handle last_id parameter
        if (!is_null($last_id)) {
            if (!ctype_digit($last_id)) {
                throw new \InvalidArgumentException("Invalid last_id parameter");
            }
            $params['last_id'] = $last_id;
        }

        // handle sort parameter
        if (!is_null($sort)) {
            if (!in_array($sort, array('asc', 'desc'))) {
                throw new \InvalidArgumentException("Invalid sort parameter");
            }
        }

        return $this->query('members/notifictions.json', $params);
    }

    /**
     * Pour voir (ou modifier si value est renseigné à 1 ou 0) l'option d'un membre identifié.
     * Options autorisées en lecture : downloaded, notation, decalage
     * Options autorisées en écriture : downloaded, notation, decalage
     *
     * @param string   $token
     * @param string   $option
     * @param null|int $value
     * @return array
     * @throws \InvalidArgumentException
     */
    public function membersOption($token, $option, $value = null)
    {
        $params = array(
            'token' => $token
        );

        // handle option parameter
        if (!in_array($option, array('downloaded', 'notation', 'decalage'))) {
            throw new \InvalidArgumentException("Invalid option parameter");
        }

        // handle value parameter
        if (!is_null($value)) {
            if (!in_array($value, array(0, 1))) {
                throw new \InvalidArgumentException("Invalid value parameter");
            }
            $params['value'] = $value;
        }

        return $this->query('members/option/' . $option . '.json', $params);
    }

    /**
     * Crée instantanément un compte avec les identifiants et le mail spécifiés. (Taille maximale pour le login : 24 caractères)
     *
     * @param string $login
     * @param string $password
     * @param string $email
     * @return array
     * @throws \InvalidArgumentException
     */
    public function membersSignup($login, $password, $email)
    {
        // handle login parameter
        if (strlen($login) > 24) {
            throw new \InvalidArgumentException("login too long");
        }

        $params = array(
            'login'    => $login,
            'password' => $password,
            'email'    => $email
        );

        return $this->query('members/signup.json', $params);
    }

    /**
     * Retourne la liste d'amis soit de l'utilisateur identifié par son token, soit de l'utilisateur spécifié dans l'URL.
     *
     * @param null|string $token
     * @param null|string $login
     * @return array
     */
    public function membersFriends($token = null, $login = null)
    {
        $params = array();

        // handle token parameter
        if (!is_null($token)) {
            $params['token'] = $token;
        }

        $url = 'members/friends.json';

        // handle login parameter
        if (!is_null($login)) {
            $url = 'members/friends/' . $login . '.json';
        }

        return $this->query($url, $params);
    }

    /**
     * Retourne la liste des badges soit de l'utilisateur identifié par son token, soit de l'utilisateur spécifié dans l'URL.
     *
     * @param null|string $token
     * @param null|string $login
     * @return array
     */
    public function membersBadges($token = null, $login = null)
    {
        $params = array();

        // handle token parameter
        if (!is_null($token)) {
            $params['token'] = $token;
        }

        $url = 'members/badges.json';

        // handle login parameter
        if (!is_null($login)) {
            $url = 'members/badges/' . $login . '.json';
        }

        return $this->query($url, $params);
    }

    /**
     * Ajoute un utilisateur comme ami.
     *
     * @param string $token
     * @param string $login
     * @return array
     */
    public function membersAdd($token, $login)
    {
        $params = array(
            'token' => $token
        );

        return $this->query('members/add/' . $login . '.json', $params);
    }

    /**
     * Enlève un utilisateur des amis de l'utilisateur identifié.
     *
     * @param string $token
     * @param string $login
     * @return array
     */
    public function membersDelete($token, $login)
    {
        $params = array(
            'token' => $token
        );

        return $this->query('members/delete/' . $login . '.json', $params);
    }

    /**
     * Liste les 10 premiers membres dont le pseudo commence par le terme login.
     *
     * @param string $login
     * @return array
     */
    public function membersSearch($login)
    {
        $params = array(
            'login' => $login
        );

        return $this->query('members/search.json', $params);
    }

    /**
     * Bloque le membre spécifié.
     *
     * @param string $token
     * @param string $login
     * @return array
     */
    public function membersBlock($token, $login)
    {
        $params = array(
            'token' => $token
        );

        return $this->query('members/block/' . $login . '.json', $params);
    }

    /**
     * Débloque le membre spécifié.
     *
     * @param string $token
     * @param string $login
     * @return array
     */
    public function membersUnblock($token, $login)
    {
        $params = array(
            'token' => $token
        );

        return $this->query('members/unblock/' . $login . '.json', $params);
    }

    /**
     * Récupère les options du membre. Pour le moment, les sources de sous-titres qu'il a sélectionnées.
     *
     * @param string $token
     * @return array
     */
    public function membersOptions($token)
    {
        $params = array(
            'token' => $token
        );

        return $this->query('members/options.json', $params);
    }

    /**
     * Retourne la liste des amis de l'utilisateur identifié par son token et qui correspondent aux adresses email soumises.
     * Le paramètre mail accepte une ou plusieurs adresses email.
     * Elles doivent être séparées par une virgule.
     *
     * @param string $token
     * @param string $mail
     * @return array
     */
    public function membersSync($token, $mail)
    {
        $params = array(
            'token' => $token,
            'mail'  => $mail
        );

        return $this->query('members/sync.json', $params);
    }

    /**
     * Affiche les commentaires de la série spécifiée.
     *
     * @param string $url
     * @return array
     */
    public function commentsShow($url)
    {
        return $this->query('comments/show/' . $url . '.json');
    }

    /**
     * Affiche les commentaires de l'épisode spécifié.
     *
     * @param string $url
     * @param int    $season
     * @param int    $episode
     * @return array
     */
    public function commentsEpisode($url, $season, $episode)
    {
        $params = array(
            'season'  => $season,
            'episode' => $episode
        );

        return $this->query('comments/episode/' . $url . '.json', $params);
    }

    /**
     * Affiche les commentaires du membre spécifié.
     *
     * @param string $login
     * @return array
     */
    public function commentsMember($login)
    {
        return $this->query('comments/member/' . $login . '.json');
    }

    /**
     * Poste un commentaire sur la fiche d'une série.
     * Vous pouvez spécifier s'il s'agit d'une réponse à un autre commentaire en précisant son ID.
     *
     * @param string   $token
     * @param string   $show
     * @param string   $text
     * @param null|int $in_reply_to
     * @return array
     * @throws \InvalidArgumentException
     */
    public function commentsPostShow($token, $show, $text, $in_reply_to = null)
    {
        $params = array(
            'token' => $token,
            'show'  => $show,
            'text'  => $text
        );

        // handle in_reply_to parameter
        if (!is_null($in_reply_to)) {
            if (!ctype_digit($in_reply_to)) {
                throw new \InvalidArgumentException('Invalid in_reply_to parameter');
            }
            $params['in_reply_to'] = $in_reply_to;
        }

        return $this->query('comments/post/show.json', $params);
    }

    /**
     * Poste un commentaire sur la fiche d'un épisode.
     * Vous pouvez spécifier s'il s'agit d'une réponse à un autre commentaire en précisant son ID.
     *
     * @param string   $token
     * @param string   $show
     * @param int      $season
     * @param int      $episode
     * @param string   $text
     * @param null|int $in_reply_to
     * @return array
     * @throws \InvalidArgumentException
     */
    public function commentsPostEpisode($token, $show, $season, $episode, $text, $in_reply_to = null)
    {
        $params = array(
            'token'   => $token,
            'show'    => $show,
            'season'  => $season,
            'episode' => $episode,
            'text'    => $text
        );

        // handle in_reply_to parameter
        if (!is_null($in_reply_to)) {
            if (!ctype_digit($in_reply_to)) {
                throw new \InvalidArgumentException('Invalid in_reply_to parameter');
            }
            $params['in_reply_to'] = $in_reply_to;
        }

        return $this->query('comments/post/episode.json', $params);
    }

    /**
     * Poste un commentaire sur le profil d'un membre.
     * Vous pouvez spécifier s'il s'agit d'une réponse à un autre commentaire en précisant son ID.
     *
     * @param string     $token
     * @param string     $member
     * @param string     $text
     * @param null|int   $in_reply_to
     * @return array
     * @throws \InvalidArgumentException
     *
     */
    public function commentsPostMember($token, $member, $text, $in_reply_to = null)
    {
        $params = array(
            'token'  => $token,
            'member' => $member,
            'text'   => $text
        );

        // handle in_reply_to parameter
        if (!is_null($in_reply_to)) {
            if (!ctype_digit($in_reply_to)) {
                throw new \InvalidArgumentException('Invalid in_reply_to parameter');
            }
            $params['in_reply_to'] = $in_reply_to;
        }

        return $this->query('comments/post/member.json', $params);
    }

    /**
     * Inscrit l'utilisateur identifié aux notifications par mail des nouveaux commentaires de l'ID de référence spécifié (ref_id est renvoyé dans les affichages de commentaires).
     *
     * @param string $token
     * @param int    $ref_id
     * @return array
     */
    public function commentsSubscribe($token, $ref_id)
    {
        $params = array(
            'token'  => $token,
            'ref_id' => $ref_id
        );

        return $this->query('comments/subscribe.json', $params);
    }

    /**
     * Désinscrit l'utilisateur identifié aux notifications par mail des nouveaux commentaires de l'ID de référence spécifié (ref_id est renvoyé dans les affichages de commentaires).
     *
     * @param string $token
     * @param int    $ref_id
     * @return array
     */
    public function commentsUnsubscribe($token, $ref_id)
    {
        $params = array(
            'token'  => $token,
            'ref_id' => $ref_id
        );

        return $this->query('comments/unsubscribe.json', $params);
    }

    /**
     * Affiche les N derniers évènements du site. Maximum 100.
     *
     * @param null|int $number
     * @return array
     * @throws \InvalidArgumentException
     */
    public function timelineHome($number = null)
    {
        $params = array();

        // handle number parameter
        if (!is_null($number)) {
            if (!ctype_digit($number) || (0 === $number) || (100 < $number)) {
                throw new \InvalidArgumentException('Invalid number parameter');
            }
            $params['number'] = $number;
        }

        return $this->query('timeline/home.json', $params);
    }

    /**
     * Affiche les N derniers évènements des amis du membre identifié. Maximum 100.
     *
     * @param string   $token
     * @param null|int $number
     * @return array
     * @throws \InvalidArgumentException
     */
    public function timelineFriends($token, $number = null)
    {
        $params = array(
            'token' => $token
        );

        // handle number parameter
        if (!is_null($number)) {
            if (!ctype_digit($number) || (0 === $number) || (100 < $number)) {
                throw new \InvalidArgumentException('Invalid number parameter');
            }
            $params['number'] = $number;
        }

        return $this->query('timeline/friends.json', $params);
    }

    /**
     * Affiche les N derniers évènements de login (l'accès varie selon les options vie privée de chaque membre). Maximum 100.
     *
     * @param string      $member
     * @param null|string $token
     * @param null|int    $number
     * @return array
     * @throws \InvalidArgumentException
     */
    public function timelineMember($member, $token = null, $number = null)
    {
        $params = array();

        // handle token parameter
        if (!is_null($token)) {
            $params['token'] = $token;
        }

        // handle number parameter
        if (!is_null($number)) {
            if (!ctype_digit($number) || (0 === $number) || (100 < $number)) {
                throw new \InvalidArgumentException('Invalid number parameter');
            }
            $params['number'] = $number;
        }

        return $this->query('timeline/member/' . $member . '.json', $params);
    }

    /**
     * Affiche la liste derniers messages dans la boîte de réception dans l'ordre antéchronologique, par page de 15.
     *
     * @param string   $token
     * @param null|int $page
     * @return array
     * @throws \InvalidArgumentException
     */
    public function messagesInbox($token, $page = null)
    {
        $params = array(
            'token' => $token
        );

        if (!is_null($page)) {
            if (!ctype_digit($page)) {
                throw new \InvalidArgumentException('Invalid page parameter');
            }
            $params['page'] = $page;
        }

        return $this->query('messages/inbox.json', $params);
    }

    /**
     * Affiche les messages de la discussion spécifiée dans l'ordre chronologique, par page de 15.
     * Note : La discussion est automatiquement marquée comme étant lue
     *
     * @param string   $token
     * @param int      $id
     * @param null|int $page
     * @return array
     * @throws \InvalidArgumentException
     */
    public function messagesDiscussion($token, $id, $page = null)
    {
        $params = array(
            'token' => $token
        );

        if (!is_null($page)) {
            if (!ctype_digit($page)) {
                throw new \InvalidArgumentException('Invalid page parameter');
            }
            $params['page'] = $page;
        }

        return $this->query('messages/discussion/' . $id . '.json', $params);
    }

    /**
     * Envoie un nouveau message, démarre une discussion en spécifiant title et récipient.
     *
     * @param string $token
     * @param string $title
     * @param string $text
     * @param string $recipient
     * @return mixed
     */
    public function messagesSendNew($token, $title, $text, $recipient)
    {
        $params = array(
            'token'     => $token,
            'title'     => $title,
            'text'      => $text,
            'recipient' => $recipient
        );

        return $this->query('messages/send.json', $params, 'post');
    }

    /**
     * Envoie un nouveau message, une réponse à une discussion en spécifiant discussion_id.
     *
     * @param string $token
     * @param string $text
     * @param int    $discussion_id
     * @return mixed
     */
    public function messagesSendResponse($token, $text, $discussion_id)
    {
        $params = array(
            'token'         => $token,
            'text'          => $text,
            'discussion_id' => $discussion_id
        );

        return $this->query('messages/send.json', $params, 'post');
    }

    /**
     * Supprime le message avec l'ID spécifié.
     * Attention, s'il s'agit du premier message d'une discussion alors toute la discussion sera supprimée.
     *
     * @param string $token
     * @param int    $id
     * @return mixed
     */
    public function messagesDelete($token, $id)
    {
        $params = array(
            'token' => $token
        );

        return $this->query('messages/delete/' . $id . '.json', $params);
    }
}