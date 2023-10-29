<?php

namespace Cis\Barcode\BarcodeType;

interface TypeInterface
{
    public function encode(
        string $data,
        string $pad = '',
        int $ecl = 0,
        int $dstate = 0,
        bool $rect = false,
        bool $fnc1 = false
    ): array;
}
