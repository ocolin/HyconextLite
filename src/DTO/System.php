<?php

declare( strict_types = 1 );

namespace Ocolin\HyconextLite\DTO;

readonly class System
{
    public function __construct(
        public string $serialNumber,
        public string $model,
        public float  $tempCelsius,
        public float  $tempFahrenheit,
        public float  $fanVcc,
        public float  $fanVccp,
        public string $ipv4,
        public string $ipv6,
        public string $ipv6LinkLocal,
        public string $macAddress,
        public string $firmwareVersion,
        public string $hardwareVersion,
        public string $description,
        public string $uptime
    ) {}
}