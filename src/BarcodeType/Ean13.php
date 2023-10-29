<?php

namespace Cis\Barcode\BarcodeType;

class Ean13 extends UpcA
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
        /* Quiet zone, start, first digit (as parity). */
        $system = substr($data, 0, 1);
        $pbits = (int)$system ? self::PARITY[$system] : [1, 1, 1, 1, 1, 1];
        $blocks[] = [
            'm' => [[0, 9, 0]],
            'l' => [$system, 0.5, 1/3]
        ];
        $blocks[] = [
            'm' => [
                [1, 1, 1],
                [0, 1, 1],
                [1, 1, 1],
            ]
        ];
        /* Left zone. */
        for ($i = 1; $i < 7; $i++) {
            $digit = substr($data, $i, 1);
            $pbit = $pbits[$i - 1];
            $blocks[] = [
                'm' => [
                    [0, self::ALPHABET[$digit][$pbit ? 0 : 3], 1],
                    [1, self::ALPHABET[$digit][$pbit ? 1 : 2], 1],
                    [0, self::ALPHABET[$digit][$pbit ? 2 : 1], 1],
                    [1, self::ALPHABET[$digit][$pbit ? 3 : 0], 1],
                ],
                'l' => [$digit, 0.5, (7 - $i) / 7]
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
        for ($i = 7; $i < 13; $i++) {
            $digit = substr($data, $i, 1);
            $blocks[] = [
                'm' => [
                    [1, self::ALPHABET[$digit][0], 1],
                    [0, self::ALPHABET[$digit][1], 1],
                    [1, self::ALPHABET[$digit][2], 1],
                    [0, self::ALPHABET[$digit][3], 1],
                ],
                'l' => [$digit, 0.5, (13 - $i) / 7]
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
            'l' => [$pad, 0.5, 2/3]
        ];
        /* Return code. */
        return ['g' => 'l', 'b' => $blocks];
    }

    /**
     * @param $data
     * @return array|string|null
     */
    protected function normalize($data): array|string|null
    {
        $data = preg_replace('/[^0-9*]/', '', $data);
        /* Set length to 13 digits. */
        if (strlen($data) < 13) {
            return '0' . parent::normalize($data);
        } elseif (strlen($data) > 13) {
            $left = substr($data, 0, 7);
            $right = substr($data, -6);
            $data = $left . $right;
        }
        /* Replace * with missing or check digit. */
        while (($o = strrpos($data, '*')) !== false) {
            $checksum = 0;
            for ($i = 0; $i < 13; $i++) {
                $digit = substr($data, $i, 1);
                $checksum += (($i % 2) ? 3 : 1) * $digit;
            }
            $checksum *= (($o % 2) ? 3 : 9);
            $left = substr($data, 0, $o);
            $center = substr($checksum, -1);
            $right = substr($data, $o + 1);
            $data = $left . $center . $right;
        }
        return $data;
    }
}
