<?php

declare( strict_types = 1 );

namespace Ocolin\HyconextLite;

use GuzzleHttp\Client AS Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Cookie\CookieJar;
use Ocolin\HyconextLite\Exceptions\AuthException;
use Ocolin\HyconextLite\Exceptions\HyconextException;

class HTTP
{
    /**
     * @var Config Configuration data object.
     */
    private readonly Config $config;

    /**
     * @var Guzzle Guzzle client for mocking.
     */
    private Guzzle $guzzle;

    /**
     * @var CookieJar Stores website cookies.
     */
    private readonly CookieJar $cookies;

    /**
     * Default Guzzle options.
     */
    private const array DEFAULTS = [
        'verify'          => false,
        'timeout'         => 20,
        'connect_timeout' => 20,
    ];

    private bool $authenticated = false;


/* CONSTRUCTOR
----------------------------------------------------------------------------- */

    /**
     * @param Config $config Configuration data object.
     * @param ?Guzzle $guzzle Guzzle client for mocking.
     */
    public function __construct(
         Config $config,
        ?Guzzle $guzzle = null
    )
    {
        $this->cookies = new CookieJar();
        $this->config = $config;
        $protocol = $this->config->ssl ? 'https' : 'http';
        $port = $this->config->ssl ? 443 : 80;
        $uri = $protocol . '://' . $this->config->host . ':' . $port;

        $this->guzzle = $guzzle ?? new Guzzle(
            array_merge(
                self::DEFAULTS,
                $config->options,
                [
                    'base_uri'    => $uri,
                    'http_errors' => false,
                    'headers' => [
                        'Accept'     => 'application/json; charset=utf-8',
                        'User-Agent' => 'HyconextLite Client 1.0',
                    ],
                ]
            )
        );
    }



/* LOGIN TO DEVICE
----------------------------------------------------------------------------- */

    /**
     * @return void
     * @throws GuzzleException HTTP transport error.
     * @throws AuthException Authentication error.
     */
    public function login() : void
    {
        $response = $this->guzzle->get( uri: '/authorize', options:[
            'cookies' => $this->cookies,
            'query'    => [
                'loginusr' => md5( $this->config->username ),
                'loginpwd' => md5(
                    substr(
                        string: $this->config->password,
                        offset: 0,
                        length: 15
                    )
                ),
            ],
        ]);

        $body = $response->getBody()->getContents();
        if( str_contains( haystack: $body, needle: 'login.html' ) ) {
            throw new AuthException( message: 'Authentication failed' );
        }

        $this->authenticated = true;
    }



/* HTTP GET METHOD
----------------------------------------------------------------------------- */

    /**
     * @param string $endpoint URI endpoint to get.
     * @return string HTTP response body.
     * @throws GuzzleException
     */
    public function get( string $endpoint ): string
    {
        if( !$this->authenticated ) {
            $this->login();
        }
        $response = $this->guzzle->get( $endpoint, [
            'cookies' => $this->cookies,
        ]);

        if( $response->getStatusCode() !== 200 ) {
            throw new HyconextException(
                message: 'Unexpected response from device: '
                . $response->getStatusCode()
            );
        }

        return $response->getBody()->getContents();
    }
}