<?php

namespace Cis\Barcode\BarcodeType;

class Code93 implements TypeInterface
{
    private const ALPHABET = [
        '0' => [1, 3, 1, 1, 1, 2,  0],
        '1' => [1, 1, 1, 2, 1, 3,  1],
        '2' => [1, 1, 1, 3, 1, 2,  2],
        '3' => [1, 1, 1, 4, 1, 1,  3],
        '4' => [1, 2, 1, 1, 1, 3,  4],
        '5' => [1, 2, 1, 2, 1, 2,  5],
        '6' => [1, 2, 1, 3, 1, 1,  6],
        '7' => [1, 1, 1, 1, 1, 4,  7],
        '8' => [1, 3, 1, 2, 1, 1,  8],
        '9' => [1, 4, 1, 1, 1, 1,  9],
        'A' => [2, 1, 1, 1, 1, 3, 10],
        'B' => [2, 1, 1, 2, 1, 2, 11],
        'C' => [2, 1, 1, 3, 1, 1, 12],
        'D' => [2, 2, 1, 1, 1, 2, 13],
        'E' => [2, 2, 1, 2, 1, 1, 14],
        'F' => [2, 3, 1, 1, 1, 1, 15],
        'G' => [1, 1, 2, 1, 1, 3, 16],
        'H' => [1, 1, 2, 2, 1, 2, 17],
        'I' => [1, 1, 2, 3, 1, 1, 18],
        'J' => [1, 2, 2, 1, 1, 2, 19],
        'K' => [1, 3, 2, 1, 1, 1, 20],
        'L' => [1, 1, 1, 1, 2, 3, 21],
        'M' => [1, 1, 1, 2, 2, 2, 22],
        'N' => [1, 1, 1, 3, 2, 1, 23],
        'O' => [1, 2, 1, 1, 2, 2, 24],
        'P' => [1, 3, 1, 1, 2, 1, 25],
        'Q' => [2, 1, 2, 1, 1, 2, 26],
        'R' => [2, 1, 2, 2, 1, 1, 27],
        'S' => [2, 1, 1, 1, 2, 2, 28],
        'T' => [2, 1, 1, 2, 2, 1, 29],
        'U' => [2, 2, 1, 1, 2, 1, 30],
        'V' => [2, 2, 2, 1, 1, 1, 31],
        'W' => [1, 1, 2, 1, 2, 2, 32],
        'X' => [1, 1, 2, 2, 2, 1, 33],
        'Y' => [1, 2, 2, 1, 2, 1, 34],
        'Z' => [1, 2, 3, 1, 1, 1, 35],
        '-' => [1, 2, 1, 1, 3, 1, 36],
        '.' => [3, 1, 1, 1, 1, 2, 37],
        ' ' => [3, 1, 1, 2, 1, 1, 38],
        '$' => [3, 2, 1, 1, 1, 1, 39],
        '/' => [1, 1, 2, 1, 3, 1, 40],
        '+' => [1, 1, 3, 1, 2, 1, 41],
        '%' => [2, 1, 1, 1, 3, 1, 42],
        '#' => [1, 2, 1, 2, 2, 1, 43], /* ($) */
        '&' => [3, 1, 2, 1, 1, 1, 44], /* (%) */
        '|' => [3, 1, 1, 1, 2, 1, 45], /* (/) */
        '=' => [1, 2, 2, 2, 1, 1, 46], /* (+) */
        '*' => [1, 1, 1, 1, 4, 1,  0],
    ];

    private const ASCIIBET = [
        '&U', '#A', '#B', '#C', '#D', '#E', '#F', '#G',
        '#H', '#I', '#J', '#K', '#L', '#M', '#N', '#O',
        '#P', '#Q', '#R', '#S', '#T', '#U', '#V', '#W',
        '#X', '#Y', '#Z', '&A', '&B', '&C', '&D', '&E',
        ' ' , '|A', '|B', '|C', '$' , '%' , '|F', '|G',
        '|H', '|I', '|J', '+' , '|L', '-' , '.' , '/' ,
        '0' , '1' , '2' , '3' , '4' , '5' , '6' , '7' ,
        '8' , '9' , '|Z', '&F', '&G', '&H', '&I', '&J',
        '&V', 'A' , 'B' , 'C' , 'D' , 'E' , 'F' , 'G' ,
        'H' , 'I' , 'J' , 'K' , 'L' , 'M' , 'N' , 'O' ,
        'P' , 'Q' , 'R' , 'S' , 'T' , 'U' , 'V' , 'W' ,
        'X' , 'Y' , 'Z' , '&K', '&L', '&M', '&N', '&O',
        '&W', '=A', '=B', '=C', '=D', '=E', '=F', '=G',
        '=H', '=I', '=J', '=K', '=L', '=M', '=N', '=O',
        '=P', '=Q', '=R', '=S', '=T', '=U', '=V', '=W',
        '=X', '=Y', '=Z', '&P', '&Q', '&R', '&S', '&T',
    ];
    const REGEX_DATA_CLEAN = '/[^0-9A-Za-z%+\/$ .-]/';

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
        $modules = $this->addBaseModules([]);

        $values = [];
        list($modules, $values) = $this->generateDataModules($data, $modules, $values);
        $modules = $this->createCheckDigit($values, $modules);

        $modules = $this->addBaseModules($modules);
        $modules[] = [1, 1, 1];

        $blocks = [['m' => $modules, 'l' => [$data]]];
        return ['g' => 'l', 'b' => $blocks];
    }

    /**
     * @param $data
     * @return array
     */
    private function encodeAscii($data): array
    {
        $modules = $this->addBaseModules([]);

        $label = '';
        $values = [];
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
                list($modules, $values) = $this->generateDataModules($ch, $modules, $values);
            }
        }
        $modules = $this->createCheckDigit($values, $modules);

        $modules = $this->addBaseModules($modules);
        $modules[] = [1, 1, 1];

        $blocks = [['m' => $modules, 'l' => [$label]]];
        return ['g' => 'l', 'b' => $blocks];
    }

    /**
     * @param array $modules
     * @return array
     */
    private function addBaseModules(array $modules): array
    {
        $modules[] = [1, 1, 1];
        $modules[] = [0, 1, 1];
        $modules[] = [1, 1, 1];
        $modules[] = [0, 1, 1];
        $modules[] = [1, 4, 1];
        $modules[] = [0, 1, 1];
        return $modules;
    }

    /**
     * @param array $values
     * @param array $modules
     * @return array
     */
    private function createCheckDigit(array $values, array $modules): array
    {
        for ($i = 0; $i < 2; $i++) {
            $index = count($values);
            $weight = 0;
            $checksum = 0;
            while ($index) {
                $index--;
                $weight++;
                $checksum += $weight * $values[$index];
                $checksum %= 47;
                $weight %= ($i ? 15 : 20);
            }
            $values[] = $checksum;
        }
        $alphabet = array_values(self::ALPHABET);
        for ($i = count($values) - 2, $n = count($values); $i < $n; $i++) {
            $block = $alphabet[$values[$i]];
            $modules = $this->encodeDataModule($block, $modules);
        }
        return $modules;
    }

    /**
     * @param array $b
     * @param array $modules
     * @return array
     */
    private function encodeDataModule(array $b, array $modules): array
    {
        $modules[] = [1, $b[0], 1];
        $modules[] = [0, $b[1], 1];
        $modules[] = [1, $b[2], 1];
        $modules[] = [0, $b[3], 1];
        $modules[] = [1, $b[4], 1];
        $modules[] = [0, $b[5], 1];
        return $modules;
    }

    /**
     * @param string $data
     * @param mixed $modules
     * @param array $values
     * @return array
     */
    private function generateDataModules(string $data, mixed $modules, array $values): array
    {
        for ($i = 0, $n = strlen($data); $i < $n; $i++) {
            $char = substr($data, $i, 1);
            $block = self::ALPHABET[$char];
            $modules = $this->encodeDataModule($block, $modules);
            $values[] = $block[6];
        }
        return [$modules, $values];
    }
}
