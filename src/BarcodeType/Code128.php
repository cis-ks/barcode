<?php

namespace Cis\Barcode\BarcodeType;

class Code128 implements TypeInterface
{
    private const ALPHABET = [
        [2, 1, 2, 2, 2, 2], [2, 2, 2, 1, 2, 2],
        [2, 2, 2, 2, 2, 1], [1, 2, 1, 2, 2, 3],
        [1, 2, 1, 3, 2, 2], [1, 3, 1, 2, 2, 2],
        [1, 2, 2, 2, 1, 3], [1, 2, 2, 3, 1, 2],
        [1, 3, 2, 2, 1, 2], [2, 2, 1, 2, 1, 3],
        [2, 2, 1, 3, 1, 2], [2, 3, 1, 2, 1, 2],
        [1, 1, 2, 2, 3, 2], [1, 2, 2, 1, 3, 2],
        [1, 2, 2, 2, 3, 1], [1, 1, 3, 2, 2, 2],
        [1, 2, 3, 1, 2, 2], [1, 2, 3, 2, 2, 1],
        [2, 2, 3, 2, 1, 1], [2, 2, 1, 1, 3, 2],
        [2, 2, 1, 2, 3, 1], [2, 1, 3, 2, 1, 2],
        [2, 2, 3, 1, 1, 2], [3, 1, 2, 1, 3, 1],
        [3, 1, 1, 2, 2, 2], [3, 2, 1, 1, 2, 2],
        [3, 2, 1, 2, 2, 1], [3, 1, 2, 2, 1, 2],
        [3, 2, 2, 1, 1, 2], [3, 2, 2, 2, 1, 1],
        [2, 1, 2, 1, 2, 3], [2, 1, 2, 3, 2, 1],
        [2, 3, 2, 1, 2, 1], [1, 1, 1, 3, 2, 3],
        [1, 3, 1, 1, 2, 3], [1, 3, 1, 3, 2, 1],
        [1, 1, 2, 3, 1, 3], [1, 3, 2, 1, 1, 3],
        [1, 3, 2, 3, 1, 1], [2, 1, 1, 3, 1, 3],
        [2, 3, 1, 1, 1, 3], [2, 3, 1, 3, 1, 1],
        [1, 1, 2, 1, 3, 3], [1, 1, 2, 3, 3, 1],
        [1, 3, 2, 1, 3, 1], [1, 1, 3, 1, 2, 3],
        [1, 1, 3, 3, 2, 1], [1, 3, 3, 1, 2, 1],
        [3, 1, 3, 1, 2, 1], [2, 1, 1, 3, 3, 1],
        [2, 3, 1, 1, 3, 1], [2, 1, 3, 1, 1, 3],
        [2, 1, 3, 3, 1, 1], [2, 1, 3, 1, 3, 1],
        [3, 1, 1, 1, 2, 3], [3, 1, 1, 3, 2, 1],
        [3, 3, 1, 1, 2, 1], [3, 1, 2, 1, 1, 3],
        [3, 1, 2, 3, 1, 1], [3, 3, 2, 1, 1, 1],
        [3, 1, 4, 1, 1, 1], [2, 2, 1, 4, 1, 1],
        [4, 3, 1, 1, 1, 1], [1, 1, 1, 2, 2, 4],
        [1, 1, 1, 4, 2, 2], [1, 2, 1, 1, 2, 4],
        [1, 2, 1, 4, 2, 1], [1, 4, 1, 1, 2, 2],
        [1, 4, 1, 2, 2, 1], [1, 1, 2, 2, 1, 4],
        [1, 1, 2, 4, 1, 2], [1, 2, 2, 1, 1, 4],
        [1, 2, 2, 4, 1, 1], [1, 4, 2, 1, 1, 2],
        [1, 4, 2, 2, 1, 1], [2, 4, 1, 2, 1, 1],
        [2, 2, 1, 1, 1, 4], [4, 1, 3, 1, 1, 1],
        [2, 4, 1, 1, 1, 2], [1, 3, 4, 1, 1, 1],
        [1, 1, 1, 2, 4, 2], [1, 2, 1, 1, 4, 2],
        [1, 2, 1, 2, 4, 1], [1, 1, 4, 2, 1, 2],
        [1, 2, 4, 1, 1, 2], [1, 2, 4, 2, 1, 1],
        [4, 1, 1, 2, 1, 2], [4, 2, 1, 1, 1, 2],
        [4, 2, 1, 2, 1, 1], [2, 1, 2, 1, 4, 1],
        [2, 1, 4, 1, 2, 1], [4, 1, 2, 1, 2, 1],
        [1, 1, 1, 1, 4, 3], [1, 1, 1, 3, 4, 1],
        [1, 3, 1, 1, 4, 1], [1, 1, 4, 1, 1, 3],
        [1, 1, 4, 3, 1, 1], [4, 1, 1, 1, 1, 3],
        [4, 1, 1, 3, 1, 1], [1, 1, 3, 1, 4, 1],
        [1, 1, 4, 1, 3, 1], [3, 1, 1, 1, 4, 1],
        [4, 1, 1, 1, 3, 1], [2, 1, 1, 4, 1, 2],
        [2, 1, 1, 2, 1, 4], [2, 1, 1, 2, 3, 2],
        [2, 3, 3, 1, 1, 1, 2]
    ];
    const REGEX_DATA_CLEAN = '/[\x80-\xFF]/';
    const REGEX_LABEL_CLEAN = '/[\x00-\x1F\x7F]/';
    const REGEX_DETECT_C = '/(^[0-9]{6,}|^[0-9]{4,}$)/';
    const REGEX_DETECT_BA = '/([\x60-\x7F])|([\x00-\x1F])/';
    const REGEX_CONSUME_C = '/(^[0-9]{2})/';
    const REGEX_DETECT_CBA = '/(^[0-9]{4,}|^[0-9]{2}$)|([\x60-\x7F])|([\x00-\x1F])/';

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
        $data = preg_replace(self::REGEX_DATA_CLEAN, '', $data);
        $labelData = str_replace('\FNC1', '', $data);
        $label = preg_replace(self::REGEX_LABEL_CLEAN, ' ', $labelData);
        $chars = $this->normalize($data, $dstate, $fnc1);

        $checksum = $chars[0] % 103;
        for ($i = 1, $n = count($chars); $i < $n; $i++) {
            $checksum += $i * $chars[$i];
            $checksum %= 103;
        }
        $chars[] = $checksum;
        $chars[] = 106;
        $modules = [];
        $modules[] = [0, 10, 0];
        foreach ($chars as $char) {
            $block = self::ALPHABET[$char];
            foreach ($block as $i => $module) {
                $modules[] = [($i & 1) ^ 1, $module, 1];
            }
        }
        $modules[] = [0, 10, 0];
        $blocks = [['m' => $modules, 'l' => [$label]]];
        return ['g' => 'l', 'b' => $blocks];
    }

    /**
     * @param string $data
     * @param int $dstate
     * @param bool $fnc1
     * @return int[]
     */
    private function normalize(string $data, int $dstate, bool $fnc1): array
    {
        $data = str_replace('\FNC1', chr(29), $data);

        $state = (($dstate > 0 && $dstate < 4) ? $dstate : 0);
        $abstate = ((abs($dstate) == 2) ? 2 : 1);
        $chars = [102 + ($state ?: $abstate)];
        if ($fnc1) {
            $chars[] = 102;
        }

        while (strlen($data)) {
            switch ($state) {
                case 0:
                    list($state, $chars) = $this->encodeStateZero($data, $abstate, $fnc1);
                    break;
                case 1:
                    list($data, $chars, $state) = $this->encodeStateOne($dstate, $data, $chars, $state);
                    break;
                case 2:
                    list($data, $chars, $state) = $this->encodeStateTwo($dstate, $data, $chars, $state);
                    break;
                case 3:
                    list($data, $chars, $state) = $this->encodeStateThree($data, $chars, $state, $abstate);
                    break;
                default:
                    break;
            }
        }
        return $chars;
    }

    /**
     * @param string $data
     * @param int $abstate
     * @param bool $fnc1
     * @return array
     */
    private function encodeStateZero(string $data, int $abstate, bool $fnc1): array
    {
        if (preg_match(self::REGEX_DETECT_CBA, $data, $m)) {
            if ($m[1]) {
                $state = 3;
            } elseif ($m[2]) {
                $state = 2;
            } else {
                $state = 1;
            }
        } else {
            $state = $abstate;
        }
        $chars = [102 + $state];
        if ($fnc1) {
            $chars[] = 102;
        }

        return [$state, $chars];
    }

    /**
     * @param int $dstate
     * @param string $data
     * @param array $chars
     * @param int $state
     * @return array
     */
    private function encodeStateOne(int $dstate, string $data, array $chars, int $state): array
    {
        if ($dstate <= 0 && preg_match(self::REGEX_DETECT_C, $data, $m)) {
            if (strlen($m[0]) % 2) {
                $data = substr($data, 1);
                $chars[] = 16 + (int)substr($m[0], 0, 1);
            }
            $state = 3;
            $chars[] = 99;
        } else {
            $ch = ord(substr($data, 0, 1));
            $data = substr($data, 1);
            if ($ch < 32) {
                $chars[] = $ch + 64;
            } elseif ($ch < 96) {
                $chars[] = $ch - 32;
            } else {
                if (preg_match(self::REGEX_DETECT_BA, $data, $m) && $m[1]) {
                    $state = 2;
                    $chars[] = 100;
                } else {
                    $chars[] = 98;
                }
                $chars[] = $ch - 32;
            }
        }
        return [$data, $chars, $state];
    }

    /**
     * @param int $dstate
     * @param mixed $data
     * @param array $chars
     * @param int $state
     * @return array
     */
    private function encodeStateTwo(int $dstate, string $data, array $chars, int $state): array
    {
        if ($dstate <= 0 && preg_match(self::REGEX_DETECT_C, $data, $m)) {
            if (strlen($m[0]) % 2) {
                $data = substr($data, 1);
                $chars[] = 16 + (int)substr($m[0], 0, 1);
            }
            $state = 3;
            $chars[] = 99;
        } else {
            $ch = ord(substr($data, 0, 1));
            $data = substr($data, 1);
            if ($ch >= 32) {
                $chars[] = $ch - 32;
            } else {
                if (preg_match(self::REGEX_DETECT_BA, $data, $m) && $m[2]) {
                    $state = 1;
                    $chars[] = 101;
                } else {
                    $chars[] = 98;
                }
                $chars[] = $ch + 64;
            }
        }
        return [$data, $chars, $state];
    }

    /**
     * @param string $data
     * @param array $chars
     * @param int $state
     * @param int $abstate
     * @return array
     */
    private function encodeStateThree(string $data, array $chars, int $state, int $abstate): array
    {
        if (preg_match(self::REGEX_CONSUME_C, $data, $m)) {
            $data = substr($data, 2);
            $chars[] = (int)$m[0];
        } else {
            if (preg_match(self::REGEX_DETECT_BA, $data, $m)) {
                $state = $m[1] ? 2 : 1;
            } else {
                $state = $abstate;
            }
            $chars[] = 102 - $state;
        }
        return [$data, $chars, $state];
    }
}
