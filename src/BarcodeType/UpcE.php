<?php

namespace Cis\Barcode\BarcodeType;

class UpcE extends UpcA
{
    const REGEX_EIGHT_DIGITS = '/^([01])(\d{6})(\d)$/';

    public function encode(string $data,
                           string $pad = '',
                           int    $ecl = 0,
                           int    $dstate = 0,
                           bool   $rect = false,
                           bool   $fnc1 = false): array
    {
        $data = $this->normalize($data);
        $blocks = [];
        /* Quiet zone, start. */
        $blocks[] = [
            'm' => [[0, 9, 0]]
        ];
        $blocks[] = [
            'm' => [
                [1, 1, 1],
                [0, 1, 1],
                [1, 1, 1],
            ]
        ];
        /* Digits */
        $system = substr($data, 0, 1) & 1;
        $check = substr($data, 7, 1);
        $pbits = self::PARITY[$check];
        for ($i = 1; $i < 7; $i++) {
            $digit = substr($data, $i, 1);
            $pbit = $pbits[$i - 1] ^ $system;
            $blocks[] = [
                'm' => [
                    [0, self::ALPHABET[$digit][$pbit ? 3 : 0], 1],
                    [1, self::ALPHABET[$digit][$pbit ? 2 : 1], 1],
                    [0, self::ALPHABET[$digit][$pbit ? 1 : 2], 1],
                    [1, self::ALPHABET[$digit][$pbit ? 0 : 3], 1],
                ],
                'l' => [$digit, 0.5, (7 - $i) / 7]
            ];
        }
        /* End, quiet zone. */
        $blocks[] = [
            'm' => [
                [0, 1, 1],
                [1, 1, 1],
                [0, 1, 1],
                [1, 1, 1],
                [0, 1, 1],
                [1, 1, 1],
            ]
        ];
        $blocks[] = ['m' => [[0, 9, 0]]];
        /* Return code. */
        return ['g' => 'l', 'b' => $blocks];
    }

    protected function normalize($data): array|string|null
    {
        $data = preg_replace('/[^0-9*]/', '', $data);
        /* If exactly 8 digits, use verbatim even if check digit is wrong. */
        if (preg_match(self::REGEX_EIGHT_DIGITS, $data, $m)) {
            return $data;
        }

        /* If unknown check digit, use verbatim but calculate check digit. */
        if (preg_match('/^([01])(\d{6})([*])$/', $data, $m)) {
            return $m[1] . $m[2] . substr(parent::normalize($data), -1);
        }

        /* Otherwise normalize to UPC-A and convert back. */
        $data = parent::normalize($data);
        return match (true) {
            preg_match('/^([01]\d{2})([0-2])0{4}(\d{3})(\d)$/', $data, $m) => $m[1] . $m[3] . $m[2] . $m[4],
            preg_match('/^([01]\d{3})0{5}(\d{2})(\d)$/', $data, $m) => $m[1] . $m[2] . '3' . $m[3],
            preg_match('/^([01]\d{4})0{5}(\d)(\d)$/', $data, $m) => $m[1] . $m[2] . '4' . $m[3],
            preg_match('/^([01]\d{5})0{4}([5-9])(\d)$/', $data, $m) => $m[1] . $m[2] . $m[3],
            default => str_repeat('0', 8)
        };
    }
}
