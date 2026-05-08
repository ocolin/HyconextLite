<?php

declare( strict_types = 1 );

namespace Ocolin\HyconextLite\DTO;

readonly class PortSetting
{
    public function __construct(
        public int    $id,
        public string $status,
        public string $spdDuplexCfg,
        public string $spdDuplexActual,
        public string $flowCtrlCfg,
        public string $flowCtrlActual,
        public string $description,
    ) {}
}