<?php

namespace Cis\Barcode\BarcodeType;

class Ean8 extends UpcBarcodeType
{
    /**
     * @param string $data
     * @param string $pad
     * @param int $ecl
     * @param int $dstate
     * @param bool $rect
     * @param bool $fnc1
     * @return array
     */
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
            'm' => [[0, 9, 0]],
            'l' => ['<', 0.5, 1/3]
        ];
        $blocks[] = [
            'm' => [
                [1, 1, 1],
                [0, 1, 1],
                [1, 1, 1],
            ]
        ];
        /* Left zone. */
        for ($i = 0; $i < 4; $i++) {
            $digit = substr($data, $i, 1);
            $blocks[] = [
                'm' => [
                    [0, self::ALPHABET[$digit][0], 1],
                    [1, self::ALPHABET[$digit][1], 1],
                    [0, self::ALPHABET[$digit][2], 1],
                    [1, self::ALPHABET[$digit][3], 1],
                ],
                'l' => [$digit, 0.5, (4 - $i) / 5]
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
        for ($i = 4; $i < 8; $i++) {
            $digit = substr($data, $i, 1);
            $blocks[] = [
                'm' => [
                    [1, self::ALPHABET[$digit][0], 1],
                    [0, self::ALPHABET[$digit][1], 1],
                    [1, self::ALPHABET[$digit][2], 1],
                    [0, self::ALPHABET[$digit][3], 1],
                ],
                'l' => [$digit, 0.5, (8 - $i) / 5]
            ];
        }
        /* End, quiet zone. */
        $blocks[] = [
            'm' => [
                [1, 1, 1],
                [0, 1, 1],
                [1, 1, 1],
            ]
        ];
        $blocks[] = [
            'm' => [[0, 9, 0]],
            'l' => ['>', 0.5, 2/3]
        ];
        /* Return code. */
        return ['g' => 'l', 'b' => $blocks];
    }

    /**
     * @param $data
     * @return array|string|null
     */
    private function normalize($data): array|string|null
    {
        $data = preg_replace('/[^0-9*]/', '', $data);
        /* Set length to 8 digits. */
        if (strlen($data) < 8) {
            $midpoint = floor(strlen($data) / 2);
            $left = substr($data, 0, $midpoint);
            $center = str_repeat('0', 8 - strlen($data));
            $right = substr($data, $midpoint);
            $data = $left . $center . $right;
        } elseif (strlen($data) > 8) {
            $left = substr($data, 0, 4);
            $right = substr($data, -4);
            $data = $left . $right;
        }
        /* Replace * with missing or check digit. */
        while (($o = strrpos($data, '*')) !== false) {
            $checksum = 0;
            for ($i = 0; $i < 8; $i++) {
                $digit = substr($data, $i, 1);
                $checksum += (($i % 2) ? 1 : 3) * $digit;
            }
            $data = $this->modulo10Checksum($o, $checksum, $data);
        }
        return $data;
    }
}
