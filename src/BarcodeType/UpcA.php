<?php

namespace Cis\Barcode\BarcodeType;

class UpcA extends UpcBarcodeType
{
    public function encode(string $data,
                           string $pad = '',
                           int    $ecl = 0,
                           int    $dstate = 0,
                           bool   $rect = false,
                           bool   $fnc1 = false): array
    {
        $data = self::normalize($data);
        $blocks = [];
        /* Quiet zone, start, first digit. */
        $digit = substr($data, 0, 1);
        $blocks[] = [
            'm' => [[0, 9, 0]],
            'l' => [$digit, 0, 1/3]
        ];
        $blocks[] = [
            'm' => [
                [1, 1, 1],
                [0, 1, 1],
                [1, 1, 1],
            ]
        ];
        $blocks[] = [
            'm' => [
                [0, self::ALPHABET[$digit][0], 1],
                [1, self::ALPHABET[$digit][1], 1],
                [0, self::ALPHABET[$digit][2], 1],
                [1, self::ALPHABET[$digit][3], 1],
            ]
        ];
        /* Left zone. */
        for ($i = 1; $i < 6; $i++) {
            $digit = substr($data, $i, 1);
            $blocks[] = [
                'm' => [
                    [0, self::ALPHABET[$digit][0], 1],
                    [1, self::ALPHABET[$digit][1], 1],
                    [0, self::ALPHABET[$digit][2], 1],
                    [1, self::ALPHABET[$digit][3], 1],
                ],
                'l' => [$digit, 0.5, (6 - $i) / 6]
            ];
        }
        /* Middle. */
        $blocks[] = [
            'm' => [
                [0, 1, 1],
                [1, 1, 1],
                [0, 1, 1],
                [1, 1, 1],
                [0, 1, 1],
            ]
        ];
        /* Right zone. */
        for ($i = 6; $i < 11; $i++) {
            $digit = substr($data, $i, 1);
            $blocks[] = [
                'm' => [
                    [1, self::ALPHABET[$digit][0], 1],
                    [0, self::ALPHABET[$digit][1], 1],
                    [1, self::ALPHABET[$digit][2], 1],
                    [0, self::ALPHABET[$digit][3], 1],
                ],
                'l' => [$digit, 0.5, (11 - $i) / 6]
            ];
        }
        /* Last digit, end, quiet zone. */
        $digit = substr($data, 11, 1);
        $blocks[] = [
            'm' => [
                [1, self::ALPHABET[$digit][0], 1],
                [0, self::ALPHABET[$digit][1], 1],
                [1, self::ALPHABET[$digit][2], 1],
                [0, self::ALPHABET[$digit][3], 1],
            ]
        ];
        $blocks[] = [
            'm' => [
                [1, 1, 1],
                [0, 1, 1],
                [1, 1, 1],
            ]
        ];
        $blocks[] = [
            'm' => [[0, 9, 0]],
            'l' => [$digit, 0, 2/3]
        ];
        /* Return code. */
        return ['g' => 'l', 'b' => $blocks];
    }

    protected function normalize($data): array|string|null
    {
        $data = preg_replace('/[^0-9*]/', '', $data);
        /* Set length to 12 digits. */
        if (strlen($data) < 5) {
            $data = str_repeat('0', 12);
        } elseif (strlen($data) < 12) {
            $system = substr($data, 0, 1);
            $edata = substr($data, 1, -2);
            $epattern = (int)substr($data, -2, 1);
            $check = substr($data, -1);
            if ($epattern < 3) {
                $left = $system . substr($edata, 0, 2) . $epattern;
                $right = substr($edata, 2) . $check;
            } elseif ($epattern < strlen($edata)) {
                $left = $system . substr($edata, 0, $epattern);
                $right = substr($edata, $epattern) . $check;
            } else {
                $left = $system . $edata;
                $right = $epattern . $check;
            }
            $center = str_repeat('0', 12 - strlen($left . $right));
            $data = $left . $center . $right;
        } elseif (strlen($data) > 12) {
            $left = substr($data, 0, 6);
            $right = substr($data, -6);
            $data = $left . $right;
        }
        /* Replace * with missing or check digit. */
        while (($o = strrpos($data, '*')) !== false) {
            $checksum = 0;
            for ($i = 0; $i < 12; $i++) {
                $digit = substr($data, $i, 1);
                $checksum += (($i % 2) ? 1 : 3) * $digit;
            }
            $data = $this->modulo10Checksum($o, $checksum, $data);
        }
        return $data;
    }
}
