<?php

namespace Cis\Barcode\Render;

use Cis\Barcode\Barcode;
use Cis\Barcode\Exceptions\CreateImageException;
use GdImage;

class ImageRender implements RenderInterface
{
    protected GdImage $image;

    public function __construct(
        private readonly Barcode $barcode,
    ) {}

    /**
     * @param string $symbology
     * @param string $data
     * @param array $options
     * @return ImageRender
     * @throws CreateImageException
     */
    public function render(string $symbology, string $data, array $options): static
    {
        list($code, $widths, $width, $height, $x, $y, $w, $h) =
            $this->barcode->encodeCalculateSize($symbology, $data, $options);
        $image = imagecreatetruecolor($width, $height);

        if ($image === false) {
            throw new CreateImageException();
        }

        imagesavealpha($image, true);
        $bgcolor = ($options['bc'] ?? 'FFF');
        $bgcolor = $this->allocateColor($image, $bgcolor);
        imagefill($image, 0, 0, $bgcolor);
        $colors = [
            ($options['cs'] ?? ''),
            ($options['cm'] ?? '000'),
            ($options['c2'] ?? 'F00'),
            ($options['c3'] ?? 'FF0'),
            ($options['c4'] ?? '0F0'),
            ($options['c5'] ?? '0FF'),
            ($options['c6'] ?? '00F'),
            ($options['c7'] ?? 'F0F'),
            ($options['c8'] ?? 'FFF'),
            ($options['c9'] ?? '000'),
        ];
        foreach ($colors as $i => $color) {
            $colors[$i] = $this->allocateColor($image, $color);
        }
        $this->renderImage($image, $code, $x, $y, $w, $h, $colors, $widths, $options);
        $this->image = $image;
        return $this;
    }

    /**
     * @param string|null $filename
     * @param bool $compressed
     * @return bool|string
     */
    public function toPng(?string $filename = null, bool $compressed = true): bool|string
    {
        return $this->outputImage(filename: $filename, compressed: $compressed);
    }

    /**
     * @param string|null $filename
     * @param bool $compressed
     * @return bool|string
     */
    public function toJpeg(?string $filename = null, bool $compressed = true): bool|string
    {
        return $this->outputImage('jpg', $filename, $compressed);
    }

    /**
     * @param string|null $filename
     * @param bool $compressed
     * @return bool|string
     */
    public function toGif(?string $filename = null, bool $compressed = true): bool|string
    {
        return $this->outputImage('igf', $filename, $compressed);
    }

    /**
     * @param string $type
     * @param string|null $filename
     * @param bool $compressed
     * @return bool|string
     */
    protected function outputImage(string $type = 'png', ?string $filename = null, bool $compressed = true): bool|string
    {
        $function = match ($type) {
            'jpg', 'jpeg' => 'imagejpeg',
            'gif' => 'imagegif',
            default => 'imagepng'
        };

        if ($filename !== null) {
            return call_user_func($function, $filename);
        } else {
            $stream = fopen('php://memory', 'r+');
            call_user_func($function, $stream);
            rewind($stream);
            $stringdata = stream_get_contents($stream);
            fclose($stream);

            return $compressed ? gzdeflate($stringdata) : $stringdata;
        }
    }

    /**
     * @param $code
     * @param ...$params
     * @return void
     */
    private function renderImage($code, ...$params): void
    {
        if ($code && isset($code['g']) && $code['g']) {
            switch ($code['g']) {
                case 'l':
                    self::renderLinear($code, ...$params);
                    break;
                case 'm':
                    $this->renderMatrix($code, ...$params);
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * @param array $code
     * @param GdImage $image
     * @param int $x
     * @param int $y
     * @param int $w
     * @param int $h
     * @param array $colors
     * @param array $widths
     * @param array $options
     * @return void
     */
    private function renderLinear(array   $code,
                                  GdImage $image,
                                  int     $x,
                                  int     $y,
                                  int     $w,
                                  int     $h,
                                  array   $colors,
                                  array   $widths,
                                  array   $options): void
    {
        $textheight = (int)($options['th'] ?? 10);
        $textsize = (int)($options['ts'] ?? 1);
        $textcolor = ($options['tc'] ?? '000');
        $textcolor = $this->allocateColor($image, $textcolor);
        $width = 0;
        foreach ($code['b'] as $block) {
            foreach ($block['m'] as $module) {
                $width += $module[1] * $widths[$module[2]];
            }
        }
        if ($width) {
            $scale = $w / $width;
            $scale = (($scale > 1) ? floor($scale) : 1);
            $x = floor($x + ($w - $width * $scale) / 2);
        } else {
            $scale = 1;
            $x = floor($x + $w / 2);
        }
        foreach ($code['b'] as $block) {
            if (isset($block['l'])) {
                $label = $block['l'][0];
                $ly = (float)($block['l'][1] ?? 1);
                $lx = (float)($block['l'][2] ?? 0.5);
                $my = round($y + min($h, $h + ($ly - 1) * $textheight));
                $ly = ($y + $h + $ly * $textheight);
                $ly = round($ly - imagefontheight($textsize));
            } else {
                $label = null;
                $my = $y + $h;
            }
            $mx = $x;
            foreach ($block['m'] as $module) {
                $mc = $colors[$module[0]];
                $mw = $mx + $module[1] * $widths[$module[2]] * $scale;
                imagefilledrectangle($image, $mx, $y, $mw - 1, $my - 1, $mc);
                $mx = $mw;
            }
            if (!is_null($label)) {
                $lx = ($x + ($mx - $x) * $lx);
                $lw = imagefontwidth($textsize) * strlen($label);
                $lx = round($lx - $lw / 2);
                imagestring($image, $textsize, $lx, $ly, $label, $textcolor);
            }
            $x = $mx;
        }
    }

    private function renderMatrix(array   $code,
                                  GdImage $image,
                                  int     $x,
                                  int     $y,
                                  int     $w,
                                  int     $h,
                                  array   $colors,
                                  array   $widths,
                                  array   $options): void
    {
        $shape = (isset($options['ms']) ? strtolower($options['ms']) : '');
        $density = (isset($options['md']) ? (float)$options['md'] : 1);
        list($width, $height) = $this->barcode->matrixCalculateSize($code, $widths);
        if ($width && $height) {
            $scale = min($w / $width, $h / $height);
            $scale = (($scale > 1) ? floor($scale) : 1);
            $x = floor($x + ($w - $width * $scale) / 2);
            $y = floor($y + ($h - $height * $scale) / 2);
        } else {
            $scale = 1;
            $x = floor($x + $w / 2);
            $y = floor($y + $h / 2);
        }
        $x += $code['q'][3] * $widths[0] * $scale;
        $y += $code['q'][0] * $widths[0] * $scale;
        $wh = $widths[1] * $scale;
        foreach ($code['b'] as $by => $row) {
            $y1 = $y + $by * $wh;
            foreach ($row as $bx => $color) {
                $x1 = $x + $bx * $wh;
                $mc = $colors[$color];
                $this->matrixDotImage($image, $x1, $y1, $wh, $wh, $mc, $shape, $density);
            }
        }
    }


    /**
     * @param GdImage $image
     * @param string $color
     * @return false|int
     */
    public function allocateColor(GdImage $image, string $color): false|int
    {
        $color = preg_replace('/[^0-9A-Fa-f]/', '', $color);
        $alpha = null;
        switch (strlen($color)) {
            case 1:
                $r = $g = $b = hexdec($color) * 17;
                break;
            case 2:
                $r = $g = $b = hexdec($color);
                break;
            case 3:
                $r = hexdec(substr($color, 0, 1)) * 17;
                $g = hexdec(substr($color, 1, 1)) * 17;
                $b = hexdec(substr($color, 2, 1)) * 17;
                break;
            case 4:
                $a = hexdec(substr($color, 0, 1)) * 17;
                $r = hexdec(substr($color, 1, 1)) * 17;
                $g = hexdec(substr($color, 2, 1)) * 17;
                $b = hexdec(substr($color, 3, 1)) * 17;
                $alpha = round((255 - $a) * 127 / 255);
                break;
            case 6:
                $r = hexdec(substr($color, 0, 2));
                $g = hexdec(substr($color, 2, 2));
                $b = hexdec(substr($color, 4, 2));
                break;
            case 8:
                $a = hexdec(substr($color, 0, 2));
                $r = hexdec(substr($color, 2, 2));
                $g = hexdec(substr($color, 4, 2));
                $b = hexdec(substr($color, 6, 2));
                $alpha = round((255 - $a) * 127 / 255);
                break;
            default:
                $r = $g = $b = 0;
                $alpha = 127;
                break;
        }
        return $alpha !== null
            ? imagecolorallocatealpha($image, $r, $g, $b, $alpha)
            : imagecolorallocate($image, $r, $g, $b);
    }

    /**
     * @param GdImage $image
     * @param int $x
     * @param int $y
     * @param int $w
     * @param int $h
     * @param int $mc
     * @param string $ms
     * @param float $md
     * @return void
     */
    public function matrixDotImage(GdImage $image, int $x, int $y, int $w, int $h, int $mc, string $ms, float $md): void
    {
        switch ($ms) {
            default:
                list($x, $y, $w, $h) = $this->calcXywh($x, $md, $w, $y, $h);
                imagefilledrectangle($image, $x, $y, $x + $w - 1, $y + $h - 1, $mc);
                break;
            case 'r':
                $cx = floor($x + $w / 2);
                $cy = floor($y + $h / 2);
                $dx = ceil($w * $md);
                $dy = ceil($h * $md);
                imagefilledellipse($image, $cx, $cy, $dx, $dy, $mc);
                break;
            case 'x':
                list($x, $y, $w, $h) = $this->calcXywh($x, $md, $w, $y, $h);
                imageline($image, $x, $y, $x + $w - 1, $y + $h - 1, $mc);
                imageline($image, $x, $y + $h - 1, $x + $w - 1, $y, $mc);
                break;
        }
    }

    /**
     * @param int $x
     * @param float $md
     * @param int $w
     * @param int $y
     * @param int $h
     * @return array
     */
    private function calcXywh(int $x, float $md, int $w, int $y, int $h): array
    {
        $x = floor($x + (1 - $md) * $w / 2);
        $y = floor($y + (1 - $md) * $h / 2);
        $w = ceil($w * $md);
        $h = ceil($h * $md);
        return [$x, $y, $w, $h];
    }
}
