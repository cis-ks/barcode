<?php

namespace Cis\Barcode\BarcodeType;

class Itf extends BarcodeType
{
    private const ALPHABET = [
        '0' => [1, 1, 2, 2, 1],
        '1' => [2, 1, 1, 1, 2],
        '2' => [1, 2, 1, 1, 2],
        '3' => [2, 2, 1, 1, 1],
        '4' => [1, 1, 2, 1, 2],
        '5' => [2, 1, 2, 1, 1],
        '6' => [1, 2, 2, 1, 1],
        '7' => [1, 1, 1, 2, 2],
        '8' => [2, 1, 1, 2, 1],
        '9' => [1, 2, 1, 2, 1],
    ];

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
        $data = preg_replace('/\D/', '', $data);
        if (strlen($data) % 2) {
            $data = '0' . $data;
        }
        $blocks = [];
        /* Quiet zone, start. */
        $blocks[] = ['m' => [[0, 10, 0]]];
        $blocks[] = ['m' => [
            [1, 1, 1],
            [0, 1, 1],
            [1, 1, 1],
            [0, 1, 1],
        ]];
        /* Data. */
        for ($i = 0, $n = strlen($data); $i < $n; $i += 2) {
            $c1 = substr($data, $i, 1);
            $c2 = substr($data, $i+1, 1);
            $b1 = self::ALPHABET[$c1];
            $b2 = self::ALPHABET[$c2];
            $blocks[] = [
                'm' => [
                    [1, 1, $b1[0]],
                    [0, 1, $b2[0]],
                    [1, 1, $b1[1]],
                    [0, 1, $b2[1]],
                    [1, 1, $b1[2]],
                    [0, 1, $b2[2]],
                    [1, 1, $b1[3]],
                    [0, 1, $b2[3]],
                    [1, 1, $b1[4]],
                    [0, 1, $b2[4]],
                ],
                'l' => [$c1 . $c2]
            ];
        }
        /* End, quiet zone. */
        $blocks[] = ['m' => [
            [1, 1, 2],
            [0, 1, 1],
            [1, 1, 1],
        ]];
        $blocks[] = ['m' => [[0, 10, 0]]];
        /* Return code. */
        return ['g' => 'l', 'b' => $blocks];
    }
}
