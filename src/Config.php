<?php

declare( strict_types = 1 );

namespace Ocolin\HyconextLite;

use Ocolin\GlobalType\ENV;
use Ocolin\HyconextLite\Exceptions\ConfigException;

readonly class Config
{
    /**
     * @var string Host or IP address of device.
     */
    public string $host;

    /**
     * @var string Username to log in with.
     */
    public string $username;

    /**
     * @var string Password to log in with.
     */
    public string $password;

    /**
     * @var bool Use SSL connection.
     */
    public bool $ssl;

    /**
     * @var array<string, mixed> Additional Guzzle options.
     */
    public array $options;

    /**
     * @param ?string $host Host or IP address of device.
     * @param ?string $username Username to log in with.
     * @param ?string $password Password to log in with.
     * @param ?bool $ssl Use SSL connection.
     * @param ?array<string, mixed> $options Additional Guzzle options.
     */
    public function __construct(
        ?string $host      = null,
        ?string $username  = null,
        ?string $password  = null,
        ?bool   $ssl       = null,
        ?array  $options = null,
    ) {
        $this->host = $host
            ?? ENV::getStringNull( name: 'HYCONEXT_LITE_HOST' )
            ?? throw new ConfigException( message: 'Host is required' );

        $this->username = $username
            ?? ENV::getStringNull( name: 'HYCONEXT_LITE_USERNAME' )
            ?? throw new ConfigException( message: 'Username is required' );

        $this->password = $password
            ?? ENV::getStringNull( name: 'HYCONEXT_LITE_PASSWORD' )
            ?? throw new ConfigException( message: 'Password is required' );

        $this->ssl = $ssl
            ?? ENV::getBoolNull( name: 'HYCONEXT_LITE_SSL' ) ?? true;

        $this->options = $options ?? [];
    }
}