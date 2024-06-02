<?php

namespace Cis\Barcode\BarcodeType;

class Codabar implements TypeInterface
{
    private const ALPHABET = [
        '0' => [1, 1, 1, 1, 1, 2, 2],
        '1' => [1, 1, 1, 1, 2, 2, 1],
        '4' => [1, 1, 2, 1, 1, 2, 1],
        '5' => [2, 1, 1, 1, 1, 2, 1],
        '2' => [1, 1, 1, 2, 1, 1, 2],
        '-' => [1, 1, 1, 2, 2, 1, 1],
        '$' => [1, 1, 2, 2, 1, 1, 1],
        '9' => [2, 1, 1, 2, 1, 1, 1],
        '6' => [1, 2, 1, 1, 1, 1, 2],
        '7' => [1, 2, 1, 1, 2, 1, 1],
        '8' => [1, 2, 2, 1, 1, 1, 1],
        '3' => [2, 2, 1, 1, 1, 1, 1],
        'C' => [1, 1, 1, 2, 1, 2, 2],
        'D' => [1, 1, 1, 2, 2, 2, 1],
        'A' => [1, 1, 2, 2, 1, 2, 1],
        'B' => [1, 2, 1, 2, 1, 1, 2],
        '*' => [1, 1, 1, 2, 1, 2, 2],
        'E' => [1, 1, 1, 2, 2, 2, 1],
        'T' => [1, 1, 2, 2, 1, 2, 1],
        'N' => [1, 2, 1, 2, 1, 1, 2],
        '.' => [2, 1, 2, 1, 2, 1, 1],
        '/' => [2, 1, 2, 1, 1, 1, 2],
        ':' => [2, 1, 1, 1, 2, 1, 2],
        '+' => [1, 1, 2, 1, 2, 1, 2],
    ];
    const REGEX_DATA_CLEAN = '/[^0-9ABCDENTabcdent*.\/:+$-]/';

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
        $data = strtoupper(preg_replace(self::REGEX_DATA_CLEAN, '', $data));
        $blocks = [];
        for ($i = 0; $i < strlen($data); $i++) {
            if ($blocks) {
                $blocks[] = [
                    'm' => [[0, 1, 3]]
                ];
            }
            $char = substr($data, $i, 1);
            $block = self::ALPHABET[$char];
            $blocks[] = [
                'm' => [
                    [1, 1, $block[0]],
                    [0, 1, $block[1]],
                    [1, 1, $block[2]],
                    [0, 1, $block[3]],
                    [1, 1, $block[4]],
                    [0, 1, $block[5]],
                    [1, 1, $block[6]],
                ],
                'l' => [$char]
            ];
        }
        return ['g' => 'l', 'b' => $blocks];
    }
}
