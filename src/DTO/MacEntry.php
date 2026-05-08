<?php

declare( strict_types = 1 );

namespace Ocolin\HyconextLite\DTO;

readonly class MacEntry
{
    public function __construct(
        public int    $id,
        public string $mac,
        public int    $fid,
        public int    $port,
        public int    $ageTimer
    ) {}

}