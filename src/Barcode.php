<?php

namespace Cis\Barcode;

use Cis\Barcode\BarcodeType\TypeInterface;

/****************************************************************************\
 *
* Barcode.php - Generate barcodes from a single PHP file. MIT license.
* Copyright (c) 2016-2018 Kreative Software.
 *
* Barcode-Class
* Copyright (c) 2023 CIS Bad Vilbel
 *
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
 *
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
 *
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
* THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
* FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
* DEALINGS IN THE SOFTWARE.
 *
* \****************************************************************************/

readonly class Barcode {

    public function __construct(
        private TypeInterface $barcodeType
    ) {}


    /**
     * @param string $symbology
     * @param string $data
     * @param array $options
     * @return array{array|null, int[], int, int, int, int, int, int}
     */
    public function encodeCalculateSize(string $symbology, string $data, array $options): array
    {
        $code = $this->encode($symbology, $data);
        $widths = [
            (int)($options['wq'] ?? 1),
            (int)($options['wm'] ?? 1),
            (int)($options['ww'] ?? 3),
            (int)($options['wn'] ?? 1),
            (int)($options['w4'] ?? 1),
            (int)($options['w5'] ?? 1),
            (int)($options['w6'] ?? 1),
            (int)($options['w7'] ?? 1),
            (int)($options['w8'] ?? 1),
            (int)($options['w9'] ?? 1),
        ];
        $size = $this->calculateSize($code, $widths);
        list($code, $scalex, $scaley) = $this->calculateScale($code, $options);
        list($code, $vert, $horiz) = $this->calculatePadding($code, $options);
        list($top, $left, $right, $bottom) = $this->calculatePosition($options, $vert, $horiz);
        list($iwidth, $iheight, $swidth, $sheight) = $this->calculateDimension(
            $size,
            $scalex,
            $left,
            $right,
            $scaley,
            $top,
            $bottom,
            $options
        );

        return [$code, $widths, $iwidth, $iheight, $left, $top, $swidth, $sheight];
    }

    /**
     * @param array $code
     * @param array $widths
     * @return array|float[]|int[]
     */
    public function calculateSize(array $code, array $widths): array
    {
        if ($code && isset($code['g']) && $code['g']) {
            return match ($code['g']) {
                'l' => $this->linearCalculateSize($code, $widths),
                'm' => $this->matrixCalculateSize($code, $widths)
            };
        }
        return [0, 0];
    }

    /**
     * @param array $code
     * @param array $widths
     * @return array
     */
    public function linearCalculateSize(array $code, array $widths): array
    {
        $width = 0;
        foreach ($code['b'] as $block) {
            foreach ($block['m'] as $module) {
                $width += $module[1] * $widths[$module[2]];
            }
        }
        return [$width, 80];
    }

    /**
     * @param array $code
     * @param array $widths
     * @return float[]|int[]
     */
    public function matrixCalculateSize(array $code, array $widths): array
    {
        return [
            ($code['q'][3] * $widths[0] + $code['s'][0] * $widths[1] + $code['q'][1] * $widths[0]),
            ($code['q'][0] * $widths[0] + $code['s'][1] * $widths[1] + $code['q'][2] * $widths[0])
        ];
    }

    /**
     * @param array|null $code
     * @param array $options
     * @return array
     */
    private function calculateScale(?array $code, array $options): array
    {
        $dscale = ($code && isset($code['g']) && $code['g'] == 'm') ? 4 : 1;
        $scale = (float)($options['sf'] ?? $dscale);
        $scalex = (float)($options['sx'] ?? $scale);
        $scaley = (float)($options['sy'] ?? $scale);
        return [$code, $scalex, $scaley];
    }

    /**
     * @param mixed $code
     * @param array $options
     * @return array
     */
    private function calculatePadding(mixed $code, array $options): array
    {
        $dpadding = ($code && isset($code['g']) && $code['g'] == 'm') ? 0 : 10;
        $padding = (int)($options['p'] ?? $dpadding);
        $vert = (int)($options['pv'] ?? $padding);
        $horiz = (int)($options['ph'] ?? $padding);
        return [$code, $vert, $horiz];
    }

    /**
     * @param array $options
     * @param mixed $vert
     * @param mixed $horiz
     * @return int[]
     */
    private function calculatePosition(array $options, mixed $vert, mixed $horiz): array
    {
        $top = (int)($options['pt'] ?? $vert);
        $left = (int)($options['pl'] ?? $horiz);
        $right = (int)($options['pr'] ?? $horiz);
        $bottom = (int)($options['pb'] ?? $vert);
        return [$top, $left, $right, $bottom];
    }

    /**
     * @param array $size
     * @param mixed $scalex
     * @param int $left
     * @param int $right
     * @param mixed $scaley
     * @param int $top
     * @param int $bottom
     * @param array $options
     * @return int[]
     */
    private function calculateDimension(array $size,
                                        mixed $scalex,
                                        int   $left,
                                        int   $right,
                                        mixed $scaley,
                                        int   $top,
                                        int   $bottom,
                                        array $options): array
    {
        $dwidth = ceil($size[0] * $scalex) + $left + $right;
        $dheight = ceil($size[1] * $scaley) + $top + $bottom;
        $iwidth = (int)($options['w'] ?? $dwidth);
        $iheight = (int)($options['h'] ?? $dheight);
        $swidth = $iwidth - $left - $right;
        $sheight = $iheight - $top - $bottom;
        return [$iwidth, $iheight, $swidth, $sheight];
    }

    /**
     * @param string $symbology
     * @param string $data
     * @return array|null
     */
    public function encode(string $symbology, string $data): ?array
    {
        return match ($symbology) {
            'upca', 'codabar', 'itf', 'itf14', 'qr', 'qrl', 'dmtx', 'dmtxs', 'code128', 'code93', 'code39', 'upce'
            => $this->barcodeType->encode($data),
            'code39ascii', 'ean128', 'gs1qrl', 'gs1dmtx', 'gs1dmtxs', 'code93ascii'
            => $this->barcodeType->encode($data, fnc1: true),
            'ean13nopad' => $this->barcodeType->encode($data, pad: ' '),
            'ean13pad', 'ean13' => $this->barcodeType->encode($data, pad: '>'),
            'code128a' => $this->barcodeType->encode($data, dstate: 1),
            'code128b' => $this->barcodeType->encode($data, dstate: 2),
            'code128c' => $this->barcodeType->encode($data, dstate: 3),
            'code128ac' => $this->barcodeType->encode($data, dstate: -1),
            'code128bc' => $this->barcodeType->encode($data, dstate: -2),
            'ean128a' => $this->barcodeType->encode($data, dstate: 1, fnc1: true),
            'ean128b' => $this->barcodeType->encode($data, dstate: 2, fnc1: true),
            'ean128c' => $this->barcodeType->encode($data, dstate: 3, fnc1: true),
            'ean128ac' => $this->barcodeType->encode($data, dstate: -1, fnc1: true),
            'ean128bc' => $this->barcodeType->encode($data, dstate: -2, fnc1: true),
            'qrm' => $this->barcodeType->encode($data, ecl: 1),
            'qrq' => $this->barcodeType->encode($data, ecl: 2),
            'qrh' => $this->barcodeType->encode($data, ecl: 3),
            'gs1qrm' => $this->barcodeType->encode($data, ecl: 1, fnc1: true),
            'gs1qrq' => $this->barcodeType->encode($data, ecl: 2, fnc1: true),
            'gs1qrh' => $this->barcodeType->encode($data, ecl: 3, fnc1: true),
            'dmtxr' => $this->barcodeType->encode($data, rect: true),
            'gs1dmtxr' => $this->barcodeType->encode($data, rect: true, fnc1: true),
            default => null
        };
    }
}
