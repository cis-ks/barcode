<?php

namespace Cis\Barcode\BarcodeType;

class Code39 implements TypeInterface
{
    private const ALPHABET = [
        '1' => [2, 1, 1, 2, 1, 1, 1, 1, 2],
        '2' => [1, 1, 2, 2, 1, 1, 1, 1, 2],
        '3' => [2, 1, 2, 2, 1, 1, 1, 1, 1],
        '4' => [1, 1, 1, 2, 2, 1, 1, 1, 2],
        '5' => [2, 1, 1, 2, 2, 1, 1, 1, 1],
        '6' => [1, 1, 2, 2, 2, 1, 1, 1, 1],
        '7' => [1, 1, 1, 2, 1, 1, 2, 1, 2],
        '8' => [2, 1, 1, 2, 1, 1, 2, 1, 1],
        '9' => [1, 1, 2, 2, 1, 1, 2, 1, 1],
        '0' => [1, 1, 1, 2, 2, 1, 2, 1, 1],
        'A' => [2, 1, 1, 1, 1, 2, 1, 1, 2],
        'B' => [1, 1, 2, 1, 1, 2, 1, 1, 2],
        'C' => [2, 1, 2, 1, 1, 2, 1, 1, 1],
        'D' => [1, 1, 1, 1, 2, 2, 1, 1, 2],
        'E' => [2, 1, 1, 1, 2, 2, 1, 1, 1],
        'F' => [1, 1, 2, 1, 2, 2, 1, 1, 1],
        'G' => [1, 1, 1, 1, 1, 2, 2, 1, 2],
        'H' => [2, 1, 1, 1, 1, 2, 2, 1, 1],
        'I' => [1, 1, 2, 1, 1, 2, 2, 1, 1],
        'J' => [1, 1, 1, 1, 2, 2, 2, 1, 1],
        'K' => [2, 1, 1, 1, 1, 1, 1, 2, 2],
        'L' => [1, 1, 2, 1, 1, 1, 1, 2, 2],
        'M' => [2, 1, 2, 1, 1, 1, 1, 2, 1],
        'N' => [1, 1, 1, 1, 2, 1, 1, 2, 2],
        'O' => [2, 1, 1, 1, 2, 1, 1, 2, 1],
        'P' => [1, 1, 2, 1, 2, 1, 1, 2, 1],
        'Q' => [1, 1, 1, 1, 1, 1, 2, 2, 2],
        'R' => [2, 1, 1, 1, 1, 1, 2, 2, 1],
        'S' => [1, 1, 2, 1, 1, 1, 2, 2, 1],
        'T' => [1, 1, 1, 1, 2, 1, 2, 2, 1],
        'U' => [2, 2, 1, 1, 1, 1, 1, 1, 2],
        'V' => [1, 2, 2, 1, 1, 1, 1, 1, 2],
        'W' => [2, 2, 2, 1, 1, 1, 1, 1, 1],
        'X' => [1, 2, 1, 1, 2, 1, 1, 1, 2],
        'Y' => [2, 2, 1, 1, 2, 1, 1, 1, 1],
        'Z' => [1, 2, 2, 1, 2, 1, 1, 1, 1],
        '-' => [1, 2, 1, 1, 1, 1, 2, 1, 2],
        '.' => [2, 2, 1, 1, 1, 1, 2, 1, 1],
        ' ' => [1, 2, 2, 1, 1, 1, 2, 1, 1],
        '*' => [1, 2, 1, 1, 2, 1, 2, 1, 1],
        '+' => [1, 2, 1, 1, 1, 2, 1, 2, 1],
        '/' => [1, 2, 1, 2, 1, 1, 1, 2, 1],
        '$' => [1, 2, 1, 2, 1, 2, 1, 1, 1],
        '%' => [1, 1, 1, 2, 1, 2, 1, 2, 1],
    ];

    private const ASCIIBET = [
        '%U', '$A', '$B', '$C', '$D', '$E', '$F', '$G',
        '$H', '$I', '$J', '$K', '$L', '$M', '$N', '$O',
        '$P', '$Q', '$R', '$S', '$T', '$U', '$V', '$W',
        '$X', '$Y', '$Z', '%A', '%B', '%C', '%D', '%E',
        ' ' , '/A', '/B', '/C', '/D', '/E', '/F', '/G',
        '/H', '/I', '/J', '/K', '/L', '-' , '.' , '/O',
        '0' , '1' , '2' , '3' , '4' , '5' , '6' , '7' ,
        '8' , '9' , '/Z', '%F', '%G', '%H', '%I', '%J',
        '%V', 'A' , 'B' , 'C' , 'D' , 'E' , 'F' , 'G' ,
        'H' , 'I' , 'J' , 'K' , 'L' , 'M' , 'N' , 'O' ,
        'P' , 'Q' , 'R' , 'S' , 'T' , 'U' , 'V' , 'W' ,
        'X' , 'Y' , 'Z' , '%K', '%L', '%M', '%N', '%O',
        '%W', '+A', '+B', '+C', '+D', '+E', '+F', '+G',
        '+H', '+I', '+J', '+K', '+L', '+M', '+N', '+O',
        '+P', '+Q', '+R', '+S', '+T', '+U', '+V', '+W',
        '+X', '+Y', '+Z', '%P', '%Q', '%R', '%S', '%T',
    ];
    const REGEX_DATA_CLEAN = '/[^0-9A-Za-z%$\/+ .-]/';

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
        if ($fnc1) {
            return $this->encodeAscii($data);
        }

        $data = strtoupper(preg_replace(self::REGEX_DATA_CLEAN, '', $data));
        $blocks = [];
        $blocks = $this->addBaseBlocks($blocks);

        for ($i = 0, $n = strlen($data); $i < $n; $i++) {
            $blocks[] = [
                'm' => [[0, 1, 3]]
            ];
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
                    [0, 1, $block[7]],
                    [1, 1, $block[8]],
                ],
                'l' => [$char]
            ];
        }
        $blocks[] = [
            'm' => [[0, 1, 3]]
        ];

        $blocks = $this->addBaseBlocks($blocks);
        /* Return */
        return ['g' => 'l', 'b' => $blocks];
    }

    /**
     * @param $data
     * @return array
     */
    private function encodeAscii($data): array
    {
        $modules = [];
        $modules = $this->addBaseAsciiBlocks($modules);

        $label = '';
        for ($i = 0, $n = strlen($data); $i < $n; $i++) {
            $char = substr($data, $i, 1);
            $ch = ord($char);
            if ($ch < 128) {
                if ($ch < 32 || $ch >= 127) {
                    $label .= ' ';
                } else {
                    $label .= $char;
                }
                $ch = self::ASCIIBET[$ch];
                for ($j = 0, $m = strlen($ch); $j < $m; $j++) {
                    $c = substr($ch, $j, 1);
                    $b = self::ALPHABET[$c];
                    $modules[] = [0, 1, 3];
                    $modules[] = [1, 1, $b[0]];
                    $modules[] = [0, 1, $b[1]];
                    $modules[] = [1, 1, $b[2]];
                    $modules[] = [0, 1, $b[3]];
                    $modules[] = [1, 1, $b[4]];
                    $modules[] = [0, 1, $b[5]];
                    $modules[] = [1, 1, $b[6]];
                    $modules[] = [0, 1, $b[7]];
                    $modules[] = [1, 1, $b[8]];
                }
            }
        }
        $modules[] = [0, 1, 3];

        $modules = $this->addBaseAsciiBlocks($modules);
        $blocks = [['m' => $modules, 'l' => [$label]]];
        return ['g' => 'l', 'b' => $blocks];
    }

    /**
     * @param array $blocks
     * @return array
     */
    private function addBaseBlocks(array $blocks): array
    {
        $blocks[] = [
            'm' => [
                [1, 1, 1], [0, 1, 2], [1, 1, 1],
                [0, 1, 1], [1, 1, 2], [0, 1, 1],
                [1, 1, 2], [0, 1, 1], [1, 1, 1],
            ],
            'l' => ['*']
        ];
        return $blocks;
    }

    /**
     * @param array $modules
     * @return array
     */
    private function addBaseAsciiBlocks(array $modules): array
    {
        $modules[] = [1, 1, 1];
        $modules[] = [0, 1, 2];
        $modules[] = [1, 1, 1];
        $modules[] = [0, 1, 1];
        $modules[] = [1, 1, 2];
        $modules[] = [0, 1, 1];
        $modules[] = [1, 1, 2];
        $modules[] = [0, 1, 1];
        $modules[] = [1, 1, 1];
        return $modules;
    }
}
