<?php

declare( strict_types = 1 );

namespace Ocolin\HyconextLite\DTO;

readonly class PortStatus
{
    public function __construct(
        public int    $id,
        public string $status,
        public string $linkStatus,
        public int    $txGoodPkt,
        public int    $txBadPkt,
        public int    $rxGoodPkt,
        public int    $rxBadPkt,
    ) {}
}