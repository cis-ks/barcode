<?php

namespace Cis\Barcode\BarcodeType;

abstract class BarcodeType implements TypeInterface
{
    public const ALPHANUMERIC_ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:';

    /**
     * @param false|int $o
     * @param float|int $checksum
     * @param array|string|null $data
     * @return string
     */
    protected function modulo10Checksum(false|int $o, float|int $checksum, array|string|null $data): string
    {
        $checksum *= (($o % 2) ? 9 : 3);
        $left = substr($data, 0, $o);
        $center = substr($checksum, -1);
        $right = substr($data, $o + 1);
        return $left . $center . $right;
    }

    /**
     * @param array $data
     * @param array $ecParams
     * @param array $ecPolynomials
     * @param array $log
     * @param array $exp
     * @return array
     */
    protected function ecDivide(array $data, array $ecParams, array $ecPolynomials, array $log, array $exp): array
    {
        $num_data = count($data);
        $num_error = $ecParams[1];
        $generator = $ecPolynomials[$num_error];
        $message = $data;
        for ($i = 0; $i < $num_error; $i++) {
            $message[] = 0;
        }
        for ($i = 0; $i < $num_data; $i++) {
            if ($message[$i]) {
                $leadterm = $log[$message[$i]];
                for ($j = 0; $j <= $num_error; $j++) {
                    $term = ($generator[$j] + $leadterm) % 255;
                    $message[$i + $j] ^= $exp[$term];
                }
            }
        }
        return array_slice($message, $num_data, $num_error);
    }
}
